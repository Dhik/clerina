<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Employee\BLL\Employee\EmployeeBLLInterface;
use App\Domain\Employee\Models\Employee;
use App\Domain\Employee\Exports\EmployeesExport;
use App\Domain\Employee\Requests\EmployeeRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Domain\Employee\Models\Attendance;

/**
 * @property EmployeeBLLInterface employeeBLL
 */
class EmployeeController extends Controller
{
    public function __construct(
        EmployeeBLLInterface $employeeBLL,
        protected Employee $employee,
    ) {
        $this->employeeBLL = $employeeBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $this->authorize('viewEmployee', Employee::class);
        return view('admin.employee.index');
    }

    public function attendance_index()
    {
        return view('admin.employee.attendance');
    }

    /**
     * Show the form for creating a new resource.
     *
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
        return view('admin.employee.show', compact('employee'));
    }
    public function showJson(Employee $employee): JsonResponse
    {
        return response()->json($employee);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  Employee  $employee
     */
    public function edit(Employee $employee)
    {
        $this->authorize('updateEmployee', Employee::class);
        return view('admin.employee.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EmployeeRequest $request
     * @param  Employee  $employee
     */
    public function update(Employee $employee, Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'barcode' => ['nullable', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'job_position' => ['required', 'string', 'max:255'],
            'job_level' => ['required', 'string', 'max:255'],
            'join_date' => ['required', 'date'],
            'resign_date' => ['nullable', 'date'],
            'status_employee' => ['required', 'string', 'max:255'],
            'end_date' => ['nullable', 'date'],
            'sign_date' => ['nullable', 'date'],
            'birth_date' => ['required', 'date'],
            'age' => ['required', 'integer'],
            'birth_place' => ['required', 'string', 'max:255'],
            'citizen_id_address' => ['required', 'string', 'max:255'],
            'residential_address' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'ptkp_status' => ['nullable', 'string', 'max:255'],
            'employee_tax_status' => ['nullable', 'string', 'max:255'],
            'tax_config' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:255'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:255'],
            'nik_npwp_16_digit' => ['nullable', 'string', 'max:255'],
            'mobile_phone' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'parent_branch_name' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'string', 'max:255'],
            'nationality_code' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:255'],
            'length_of_service' => ['nullable', 'integer'],
            'payment_schedule' => ['nullable', 'string', 'max:255'],
            'approval_line' => ['nullable', 'string', 'max:255'],
            'manager' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:255'],
            'cost_center_category' => ['nullable', 'string', 'max:255'],
            'sbu' => ['nullable', 'string', 'max:255'],
            'npwp_16_digit' => ['nullable', 'string', 'max:255'],
            'passport' => ['nullable', 'string', 'max:255'],
            'passport_expiration_date' => ['nullable', 'date'],
            'kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png'],
            'ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png'],
            'ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png'],
            'cv' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        try {
            if ($request->hasFile('profile_picture')) {
                $profilePicture = $request->file('profile_picture')->store('profile_pictures', 'public');
                $validatedData['profile_picture'] = $profilePicture;
            }
            if ($request->hasFile('kk')) {
                $kk = $request->file('kk')->store('kk_files', 'public');
                $validatedData['kk'] = $kk;
            }

            if ($request->hasFile('ktp')) {
                $ktp = $request->file('ktp')->store('ktp_files', 'public');
                $validatedData['ktp'] = $ktp;
            }

            if ($request->hasFile('ijazah')) {
                $ijazah = $request->file('ijazah')->store('ijazah_files', 'public');
                $validatedData['ijazah'] = $ijazah;
            }

            if ($request->hasFile('cv')) {
                $cv = $request->file('cv')->store('cv_files', 'public');
                $validatedData['cv'] = $cv;
            }

            $employee->update($validatedData);
            return redirect()
                ->route('employee.show', $employee->id)
                ->with([
                    'alert' => 'success',
                    'message' => trans('messages.success_update', ['model' => trans('labels.employee')]),
                ]);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => trans('messages.error_update', ['error' => $e->getMessage()])]);
        }
    }

    public function getOverview(Request $request)
    {
        $totalEmployees = Employee::whereNull('resign_date')->count();
        $newHires = Employee::whereMonth('join_date', now()->month)->whereYear('join_date', now()->year)->count();
        $leavings = Employee::whereNotNull('resign_date')->count();

        return response()->json([
            'totalEmployees' => $totalEmployees,
            'newHires' => $newHires,
            'leavings' => $leavings,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Employee $employee
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            // Attempt to find the related user based on the employee's employee_id
            $user = User::where('employee_id', $employee->employee_id)->first();

            // If a user is found, delete the user
            if ($user) {
                $user->delete();
            }

            // Delete the employee
            $employee->delete();

            return response()->json(['message' => trans('messages.success_delete')], 200);
        } catch (\Exception $e) {
            // If any error occurs, return a failure response
            return response()->json(['message' => trans('messages.error_delete')], 500);
        }
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
            ->addColumn('actions', function ($row) {
                return '<a href="' . route('employee.show', $row->id) . '" class="btn btn-primary btn-xs">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('employee.edit', $row->id) . '" class="btn btn-success btn-xs">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button class="btn btn-danger btn-xs deleteButton" data-id="' . $row->id . '">
                        <i class="fas fa-trash-alt"></i>
                    </button>';
            })

            ->rawColumns(['actions'])
            ->toJson();
        // return DataTables::of($userQuery)

    }
    public function performances()
    {
        return view('admin.employee.performances');
    }
    public function getNewHires(Request $request)
    {
        $query = Employee::whereMonth('join_date', now()->month)
            ->whereYear('join_date', now()->year);

        $employees = $query->get();

        return DataTables::of($employees)
            ->addColumn('actions', function ($employee) {
                return '<a href="' . route('employee.show', $employee->id) . '" class="btn btn-primary btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('employee.edit', $employee->id) . '" class="btn btn-success btn-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </a>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function getLeavings(Request $request)
    {
        $query = Employee::whereNotNull('resign_date');

        $employees = $query->get();

        return DataTables::of($employees)
            ->addColumn('actions', function ($employee) {
                return '<a href="' . route('employee.show', $employee->id) . '" class="btn btn-primary btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('employee.edit', $employee->id) . '" class="btn btn-success btn-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </a>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function getActiveEmployees(Request $request)
    {
        $query = Employee::whereNull('resign_date');

        $employees = $query->get();

        return DataTables::of($employees)
            ->addColumn('actions', function ($employee) {
                return '<a href="' . route('employee.show', $employee->id) . '" class="btn btn-primary btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('employee.edit', $employee->id) . '" class="btn btn-success btn-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </a>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
    public function getWeeklyWorkHours()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $employees = Employee::all();
        $data = [];

        foreach ($employees as $employee) {
            $attendances = Attendance::where('employee_id', $employee->employee_id)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->get();

            $totalWorkHours = 0;

            foreach ($attendances as $attendance) {
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockIn = Carbon::parse($attendance->clock_in);
                    $clockOut = Carbon::parse($attendance->clock_out);
                    $totalWorkHours += $clockOut->diffInMinutes($clockIn) / 60;
                }
            }

            $data[] = [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
                'totalWorkHours' => $totalWorkHours,
                'organization' => $employee->organization
            ];
        }

        return response()->json($data);
    }
    public function export(Request $request)
    {
        $filename = 'employees_export_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new EmployeesExport, $filename);
    }
}
