<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Employee\BLL\Employee\EmployeeBLLInterface;
use App\Domain\Employee\Models\Employee;
use App\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Domain\Employee\Models\Attendance;
use App\Domain\Employee\Models\Overtime;
use App\Domain\Employee\Models\TimeOff;
use App\Domain\Employee\Models\AttendanceRequest;
use App\Domain\Employee\Models\RequestChangeShift;

class PayrollController extends Controller
{
    protected $takeHomePay = 10000000;

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
        // $this->authorize('viewEmployee', Employee::class);
        return view('admin.payroll.index');
    }

    public function attendance_index()
    {
        return view('admin.employee.attendance');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.employee.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EmployeeRequest $request
     */
    public function store(EmployeeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $employee
     */
    public function show(Employee $employee)
    {
        $this->authorize('viewEmployee', Employee::class);

        $currentDate = Carbon::now();

        // Define date ranges for the current and previous months
        $startOfCurrentMonth = $currentDate->copy()->startOfMonth(); // 1st of the current month
        $endOfCurrentMonth = $currentDate->copy()->startOfMonth()->addDays(20); // 20th of the current month

        $startOfPreviousMonth = $currentDate->copy()->subMonth()->startOfMonth()->addDays(20); // 21st of the previous month
        $endOfPreviousMonth = $currentDate->copy()->subMonth()->endOfMonth(); // End of the previous month

        // Fetch data for the defined date ranges
        $attendances = Attendance::where('employee_id', $employee->employee_id)
            ->where(function ($query) use ($startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth) {
                $query->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                    ->orWhereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')]);
            })
            ->orderBy('date', 'asc')
            ->get();

        $overtimes = Overtime::where('employee_id', $employee->employee_id)
            ->where('status_approval', 'approved')
            ->where(function ($query) use ($startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth) {
                $query->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                    ->orWhereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')]);
            })
            ->orderBy('date', 'asc')
            ->get();

        $timeOffs = TimeOff::where('employee_id', $employee->employee_id)
            ->where('status_approval', 'approved')
            ->where(function ($query) use ($startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth) {
                $query->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                    ->orWhereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')]);
            })
            ->orderBy('date', 'asc')
            ->get();

        $attendanceRequests = AttendanceRequest::where('employee_id', $employee->employee_id)
            ->where('status_approval', 'approved')
            ->where(function ($query) use ($startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth) {
                $query->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                    ->orWhereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')]);
            })
            ->orderBy('date', 'asc')
            ->get();

        $requestChangeShifts = RequestChangeShift::where('employee_id', $employee->employee_id)
            ->where('status_approval', 'approved')
            ->where(function ($query) use ($startOfCurrentMonth, $endOfCurrentMonth, $startOfPreviousMonth, $endOfPreviousMonth) {
                $query->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
                    ->orWhereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')]);
            })
            ->orderBy('date', 'asc')
            ->get();

        // Calculate total work hours from attendances
        $totalWorkHours = $attendances->sum(function ($attendance) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);
            return $clockIn->diffInHours($clockOut);
        });

        // Calculate total attendance days
        $attendanceDays = $attendances->groupBy('date')->count();

        // Calculate salary deductions and net salary
        $salaryPerDay = $this->takeHomePay / 26;
        $baseSalary = $attendanceDays * $salaryPerDay;

        $salaryDeductions = $timeOffs->sum(function ($timeOff) {
            $deductibleTypes = ['work_from_home', 'izin 5 jam', 'izin 6 jam', 'izin 7 jam'];
            return in_array($timeOff->time_off_type, $deductibleTypes) ? 20000 : 0;
        });

        $netSalary = $baseSalary - $salaryDeductions;
        $takeHomePay = 10000000;
        return view('admin.payroll.show', compact('employee', 'attendances', 'overtimes', 'timeOffs', 'attendanceRequests', 'requestChangeShifts', 'totalWorkHours', 'netSalary', 'salaryDeductions', 'baseSalary', 'salaryPerDay', 'attendanceDays', 'takeHomePay'));
    }

    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewEmployee', Employee::class);

        $query = $this->employee->query();

        if (!is_null($request->input('date'))) {
            $attendanceDateString = Carbon::createFromFormat('Y-m-d', $request->input('date'))->format('Y-m-d');
            $query->whereDate('created_at', $attendanceDateString);
        }

        $query->orderBy('created_at', 'ASC');
        $result = $query->get();

        return DataTables::of($result)
            ->addColumn('actions', '<a href="{{ URL::route(\'payroll.show\', array($id)) }}" class="btn btn-primary btn-xs"><i class="fas fa-eye"></i></a>')
            ->rawColumns(['actions'])
            ->toJson();
    }
}
