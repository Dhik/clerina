<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Employee\BLL\Employee\EmployeeBLLInterface;
use App\Domain\Employee\Models\Employee;
use App\Domain\Employee\Models\Payroll;
use App\Domain\Employee\Models\Location;
use App\Domain\Employee\Import\PayrollImport;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Domain\Employee\Models\Attendance;
use App\Domain\Employee\Models\TimeOff;

class PayrollController extends Controller
{
    protected $takeHomePay = 7000000;

    public function __construct(
        EmployeeBLLInterface $employeeBLL,
        protected Employee $employee,
    ) {
        $this->employeeBLL = $employeeBLL;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.payroll.index');
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $employee
     */
    public function show(Employee $employee)
    {
        return view('admin.payroll.show', compact('employee'));
    }

    public function getPayrollData(Employee $employee)
    {
        $dateRanges = $this->cutOff();

        // Fetch data for the defined date ranges
        $timeOffs = TimeOff::where('employee_id', $employee->employee_id)
            ->where('status_approval', 'approved')
            ->where('time_off_type', 'Izin')
            ->orderBy('date', 'asc');
        $timeOffs = $this->filterByDateRange($timeOffs, $dateRanges['startOfCurrentMonth'], $dateRanges['endOfCurrentMonth'], $dateRanges['startOfPreviousMonth'], $dateRanges['endOfPreviousMonth'])->get();

        // Fetch payroll and place details
        $payroll = Payroll::where('employee_id', $employee->employee_id)->first();
        $place = Location::where('id', $employee->place_id)->first();

        // Initialize salary variables
        $salary = 0;
        $attendanceDaysQuery = Attendance::where('employee_id', $employee->employee_id);
        $attendanceDaysQuery = $this->filterByDateRange($attendanceDaysQuery, $dateRanges['startOfCurrentMonth'], $dateRanges['endOfCurrentMonth'], $dateRanges['startOfPreviousMonth'], $dateRanges['endOfPreviousMonth']);
        $attendanceDays = $attendanceDaysQuery->groupBy('date')->count();

        $count_time_off = $timeOffs->count();
        $salaryDeductions = 0;

        if ($place && $place->setting_name == 'Warehouse') {
            $count_attendanceQuery = Attendance::where('employee_id', $employee->employee_id)
                ->where('attendance_status', 'present');
            $count_attendanceQuery = $this->filterByDateRange($count_attendanceQuery, $dateRanges['startOfCurrentMonth'], $dateRanges['endOfCurrentMonth'], $dateRanges['startOfPreviousMonth'], $dateRanges['endOfPreviousMonth']);
            $count_attendance = $count_attendanceQuery->count();

            $salary = ($payroll->gaji_pokok / 26 * $count_attendance) + ($count_attendance * 10000) + ($payroll->gaji_pokok / (26 * 7)) * $payroll->insentif;
        } elseif ($place && $place->setting_name == 'Office') {
            $salaryDeductions = ($payroll->gaji_pokok / 26 + 20000) * $count_time_off;
            $salary = ($payroll->gaji_pokok + $payroll->function + 520000 + $payroll->insentif) - $salaryDeductions;
        }

        $salaryPerDay = $payroll->gaji_pokok / 26;
        $baseSalary = $attendanceDays * $salaryPerDay;
        $netSalary = $salary;

        return [
            'employee' => $employee,
            'timeOffs' => $timeOffs,
            'netSalary' => $this->formatNumber($netSalary),
            'salaryDeductions' => $this->formatNumber($salaryDeductions),
            'baseSalary' => $this->formatNumber($baseSalary),
            'salaryPerDay' => $this->formatNumber($salaryPerDay),
            'attendanceDays' => $attendanceDays,
            'payroll' => $payroll
        ];
    }

    private function formatNumber($number)
    {
        return number_format($number, 2, ',', '.');
    }

    public function getAttendanceData(Employee $employee)
    {
        $dateRanges = $this->cutOff();

        // Fetch data for the defined date ranges
        $attendances = Attendance::where('employee_id', $employee->employee_id)
            ->orderBy('date', 'asc');
        $attendances = $this->filterByDateRange($attendances, $dateRanges['startOfCurrentMonth'], $dateRanges['endOfCurrentMonth'], $dateRanges['startOfPreviousMonth'], $dateRanges['endOfPreviousMonth'])->get();

        return response()->json($attendances);
    }

    private function filterByDateRange($query, $startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth)
    {
        return $query->where(function ($query) use ($startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth) {
            $query->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                ->orWhereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')]);
        });
    }

    private function cutOff()
    {
        $currentDate = Carbon::now();

        return [
            'startOfCurrentMonth' => $currentDate->copy()->startOfMonth(), // 1st of the current month
            'endOfCurrentMonth' => $currentDate->copy()->startOfMonth()->addDays(20), // 20th of the current month
            'startOfPreviousMonth' => $currentDate->copy()->subMonth()->startOfMonth()->addDays(20), // 21st of the previous month
            'endOfPreviousMonth' => $currentDate->copy()->subMonth()->endOfMonth(), // End of the previous month
        ];
    }

    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewEmployee', Employee::class);

        $query = $this->employee->query()->whereNull('resign_date');

        if (!is_null($request->input('date'))) {
            $attendanceDateString = Carbon::createFromFormat('Y-m-d', $request->input('date'))->format('Y-m-d');
            $query->whereDate('created_at', $attendanceDateString);
        }

        $query->orderBy('created_at', 'ASC');
        $result = $query->get();

        return DataTables::of($result)
            ->addColumn('gaji_pokok', function ($employee) {
                $payroll = Payroll::where('employee_id', $employee->employee_id)->first();
                return $payroll ? ($payroll->gaji_pokok !== null ? $payroll->gaji_pokok : 'input gaji pokok') : 'please import payroll data';
            })
            ->addColumn('netSalary', function ($employee) {
                if ($employee->location_id) {
                    $payroll = Payroll::where('employee_id', $employee->employee_id)->first();
                    return $payroll ? $this->getPayrollData($employee)['netSalary'] : 'please import payroll data';
                } else {
                    return 'Assign to a location';
                }
            })
            ->addColumn('salaryDeductions', function ($employee) {
                if ($employee->location_id) {
                    $payroll = Payroll::where('employee_id', $employee->employee_id)->first();
                    return $payroll ? $this->getPayrollData($employee)['salaryDeductions'] : 'please import payroll data';
                } else {
                    return 'Assign to a location';
                }
            })
            ->addColumn('insentif', function ($employee) {
                $payroll = Payroll::where('employee_id', $employee->employee_id)->first();
                return $payroll ? $payroll->insentif : 'please import payroll data';
            })
            ->addColumn('actions', function ($employee) {
                if ($employee->location_id) {
                    return '<a href="' . route('payroll.show', $employee->id) . '" class="btn btn-primary btn-xs"><i class="fas fa-eye"></i></a>';
                } else {
                    return 'Assign to a location';
                }
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function importPage()
    {
        return view('admin.payroll.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        try {
            Excel::import(new PayrollImport, $request->file('file'));

            return response()->json(['success' => true, 'message' => 'Payrolls imported successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error importing payrolls: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $payroll = Payroll::findOrFail($id);
        return view('admin.payroll.edit', compact('payroll'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'gaji_pokok' => 'numeric',
            'tunjangan_jabatan' => 'numeric',
            'insentif_live' => 'numeric',
            'insentif' => 'numeric',
            'function' => 'numeric',
            'bpjs' => 'numeric',
        ]);

        $payroll = Payroll::findOrFail($id);
        $payroll->update($request->all());

        return redirect()->route('payroll.index')->with('success', 'Payroll updated successfully.');
    }

    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return response()->json(['success' => true]);
    }

    public function getPayrollsData()
    {
        $payrolls = Payroll::select(['id', 'employee_id', 'full_name', 'gaji_pokok', 'tunjangan_jabatan', 'insentif_live', 'insentif', 'function', 'bpjs', 'created_at', 'updated_at']);
        return DataTables::of($payrolls)
            ->addColumn('action', function ($payroll) {
                return '
                <a href="' . route('payroll.edit', $payroll->id) . '" class="btn btn-sm btn-primary">Edit</a>
                <button class="btn btn-sm btn-danger deleteButton" data-id="' . $payroll->id . '">Delete</button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
