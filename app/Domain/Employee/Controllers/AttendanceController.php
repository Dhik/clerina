<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Utilities\Request;
use Auth;
use App\Domain\Employee\Models\Attendance;
use Carbon\Carbon;
use App\Domain\Employee\BLL\Employee\EmployeeBLLInterface;
use App\Domain\Employee\BLL\Attendance\AttendanceBLLInterface;
use App\Domain\Employee\Models\Employee;
use App\Domain\Employee\Models\Shift;
use App\Domain\Employee\Models\AttendanceRequest;
use App\Domain\Employee\Requests\EmployeeRequest;
use App\Domain\User\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\Overtime;

/**
 * @property AttendanceBLLInterface employeeBLL
 */

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceBLLInterface $attendanceBLL,
        protected Attendance $attendance,
    ) {}

    public function attendance_log()
    {
        $this->authorize('viewAttendance', Employee::class);
        $shifts = Shift::all();
        return view('admin.attendance.index', compact('shifts'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $clockInTime = Carbon::now();
        $employee = Employee::where('employee_id', $employeeId)->first();
        $shiftId = $employee->shift_id;

        $existingAttendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', Carbon::today())
            ->first();

        if ($existingAttendance) {
            $existingAttendance->update([
                'attendance_status' => 'present',
                'clock_in' => $clockInTime,
            ]);
        }

        return redirect()->route('attendance.absensi');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $clockOutTime = Carbon::now();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereNull('clock_out')
            ->whereDate('date', Carbon::today())
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => $clockOutTime,
            ]);
        }

        return redirect()->route('attendance.absensi');
    }

    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAttendance', Employee::class);

        $query = $this->attendance->query();

        if (!is_null($request->input('date'))) {
            $attendanceDateString = Carbon::createFromFormat('Y-m-d', $request->input('date'))->format('Y-m-d');
            $query->whereDate('attendances.created_at', $attendanceDateString);
        }

        if (!is_null($request->input('employee_id'))) {
            $query->where('attendances.employee_id', $request->input('employee_id'));
        }

        $query->join('employees', 'attendances.employee_id', '=', 'employees.employee_id')
            ->leftJoin('shifts', 'employees.shift_id', '=', 'shifts.id')
            ->select(
                'attendances.*',
                'employees.full_name',
                'employees.employee_id as emp_id',
                'employees.profile_picture',
                'employees.shift_id',
                'shifts.shift_name',
                'shifts.schedule_in',
                'shifts.schedule_out',
                'employees.job_position',
            );

        $result = $query->get()->map(function ($attendance) {
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;
            $scheduleOut = Carbon::parse($attendance->schedule_out);
            $overtime = $clockOut && $clockOut->gt($scheduleOut) ? $clockOut->diff($scheduleOut)->format('%H:%I') : '-';

            return [
                'id' => $attendance->id,
                'employee_name' => $attendance->full_name,
                'employee_id' => $attendance->emp_id,
                'date' => Carbon::parse($attendance->created_at)->format('Y-m-d'),
                'shift' => $attendance->shift_name ?? '-',
                'schedule_in' => $attendance->schedule_in ?? '-',
                'schedule_out' => $attendance->schedule_out ?? '-',
                'clock_in' => $clockIn ? $clockIn->format('H:i') : '-',
                'clock_out' => $clockOut ? $clockOut->format('H:i') : '-',
                'attendance_code' => 'H',
                'time_off_code' => '-',
                'overtime' => $overtime,
                'profile_picture' => $attendance->profile_picture,
                'job_position' => $attendance->job_position,
                'shift_id' => $attendance->shift_id,
            ];
        });

        return DataTables::of($result)
            ->addColumn(
                'actions',
                '<a href="#" class="btn btn-sm btn-success" id="attendanceEdit"><i class="fas fa-pencil-alt"></i></a>
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow"><i class="fas fa-eye"></i></a>
                <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>'
            )
            ->rawColumns(['actions'])
            ->toJson();
    }



    public function getOverviewById(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;

        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $lateClockInCount = $attendances->where('attendance_status', 'present')
            ->where('clock_in', '>', $date->copy()->setTime(8, 0))->count();
        $earlyClockOutCount = $attendances->where('attendance_status', 'present')
            ->where('clock_out', '<', $date->copy()->setTime(16, 30))->count();
        $absentCount = $attendances->where('attendance_status', 'absent')->count();
        $noClockInCount = $attendances->whereNull('clock_in')->count();
        $noClockOutCount = $attendances->whereNull('clock_out')->count();

        return response()->json([
            'lateClockInCount' => $lateClockInCount,
            'earlyClockOutCount' => $earlyClockOutCount,
            'absentCount' => $absentCount,
            'noClockInCount' => $noClockInCount,
            'noClockOutCount' => $noClockOutCount,
        ]);
    }

    public function getAttendanceHistory(Request $request)
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;

        $currentDate = Carbon::now();

        // Define date ranges for the current and previous months
        $startOfCurrentMonth = $currentDate->copy()->startOfMonth()->addDays(0); // 1st of the current month
        $endOfCurrentMonth = $currentDate->copy()->startOfMonth()->addDays(20); // 20th of the current month

        $startOfPreviousMonth = $currentDate->copy()->subMonth()->startOfMonth()->addDays(20); // 20th of the previous month
        $endOfPreviousMonth = $currentDate->copy()->subMonth()->endOfMonth(); // End of the previous month

        // Fetch attendance records for the defined date ranges
        $attendancesCurrentMonth = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startOfCurrentMonth->format('Y-m-d'), $endOfCurrentMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $attendancesPreviousMonth = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startOfPreviousMonth->format('Y-m-d'), $endOfPreviousMonth->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Generate date sequences
        $currentMonthDates = $this->generateDateRange($startOfCurrentMonth, $endOfCurrentMonth);
        $previousMonthDates = $this->generateDateRange($startOfPreviousMonth, $endOfPreviousMonth);

        // Prepare the combined date range
        $combinedDates = array_merge($previousMonthDates, $currentMonthDates);

        // Populate attendance data or empty values
        $attendanceData = [];
        foreach ($combinedDates as $date) {
            $attendanceData[$date] = $attendancesPreviousMonth->get($date) ?? $attendancesCurrentMonth->get($date) ?? [
                'shift_id' => null,
                'attendance_status' => '-',
                'clock_in' => null,
                'clock_out' => null,
                'date' => $date,
                'created_at' => null,
                'updated_at' => null,
                'employee_id' => $employeeId,
            ];
        }

        return response()->json($attendanceData);
    }

    private function generateDateRange($startDate, $endDate)
    {
        $dates = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }




    public function getOverview(Request $request)
    {
        $query = Attendance::query();

        if ($request->has('date') && !is_null($request->date)) {
            $date = Carbon::parse($request->date);
            $query->whereDate('date', $date);
        }

        if ($request->has('employee_id') && !is_null($request->employee_id)) {
            $query->where('employee_id', $request->employee_id);
        }

        $attendances = $query->get();

        // No need for comparisonDate as we are not filtering by today's date unless explicitly provided
        $onTimeCount = $attendances->where('clock_in', '<=', Carbon::parse('08:15'))->count();
        $lateClockInCount = $attendances->where('clock_in', '>', Carbon::parse('08:15'))->count();
        $earlyClockOutCount = $attendances->where('clock_out', '<', Carbon::parse('16:30'))->count();
        $absentCount = $attendances->where('attendance_status', 'absent')->count();
        $noClockInCount = $attendances->whereNull('clock_in')->count();
        $noClockOutCount = $attendances->whereNull('clock_out')->count();
        $invalidCount = 0;
        $dayOffCount = 0; 
        $timeOffCount = 0;

        return response()->json([
            'onTimeCount' => $onTimeCount,
            'lateClockInCount' => $lateClockInCount,
            'earlyClockOutCount' => $earlyClockOutCount,
            'absentCount' => $absentCount,
            'noClockInCount' => $noClockInCount,
            'noClockOutCount' => $noClockOutCount,
            'invalidCount' => $invalidCount,
            'dayOffCount' => $dayOffCount,
            'timeOffCount' => $timeOffCount,
        ]);
    }



    public function show()
    {
        $this->authorize('accessAttendance', Employee::class);
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('created_at', $today)
            ->first();

        return view('admin.employee.attendance', compact('attendance'));
    }

    public function absensi()
    {
        $this->authorize('accessAttendance', Employee::class);

        $user = Auth::user();
        $employeeId = $user->employee_id;
        $today = Carbon::today();

        // Get employee details
        $employee = Employee::where('employee_id', $employeeId)->with('location')->first();

        // Get shift details
        $shift = Shift::find($employee->shift_id);

        // Get attendance details
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('created_at', $today)
            ->first();

        // Check if the employee has a location and fetch lat/long from location table
        $targetLat = $employee->location ? $employee->location->lat : null;
        $targetLng = $employee->location ? $employee->location->long : null;

        // Pass data to the view
        return view('admin.employee.new_app', [
            'attendance' => $attendance,
            'employee' => $employee,
            'shift' => $shift,
            'profile_picture' => $employee->profile_picture,
            'full_name' => $employee->full_name,
            'shift_name' => $shift->shift_name,
            'targetLat' => $targetLat,
            'targetLng' => $targetLng,
        ]);
    }


    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        $employee = Employee::where('employee_id', $attendance->employee_id)->first();
        // Get the shift name by joining employees.shift_id with shifts.id
        $shift = Shift::find($employee->shift_id);
        $shiftName = $shift ? $shift->shift_name : null;

        return response()->json([
            'id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'employee_name' => $employee->full_name,
            'created_at' => $attendance->created_at->format('Y-m-d'),
            'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
            'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
            'shift_id' => $employee->shift_id,
            'shift_name' => $shiftName,
            'schedule_in' => $shift->schedule_in,
            'schedule_out' => $shift->schedule_out,
        ]);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $employee = Employee::where('employee_id', $attendance->employee_id)->first();

        $employee->update([
            'shift_id' => $request->input('shift_id')
        ]);

        $attendance->update([
            'created_at' => $request->input('created_at'),
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
        ]);

        return redirect()->route('attendance_log.index')->with('success', 'Attendance updated successfully');
    }
    public function destroy(Attendance $attendance): JsonResponse
    {
        // $this->authorize('delete', $attendance);
        $attendance->delete();
        return response()->json(['success' => 'Attendance record deleted successfully']);
    }
    public function log()
    {
        $shifts = Shift::all();
        return view('admin.attendance.log.index', compact('shifts'));
    }
    public function overtime()
    {
        $employeeId = Auth::id(); // Assuming the employee is authenticated
        $overtimes = Overtime::where('employee_id', $employeeId)->get();
        return view('admin.attendance.overtime.index', compact('overtimes'));
    }

    public function timeoff()
    {
        return view('admin.attendance.timeoff.index');
    }

    public function store_request(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'shift_id' => 'required|integer',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i',
            'work_note' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;
            $validatedData['employee_id'] = $employeeId;
            $validatedData['status_approval'] = 'Pending';

            $clockInDateTime = Carbon::createFromFormat('H:i', $request->clock_in)->format('Y-m-d H:i:s');
            $clockOutDateTime = Carbon::createFromFormat('H:i', $request->clock_out)->format('Y-m-d H:i:s');
            $validatedData['clock_in'] = $clockInDateTime;
            $validatedData['clock_out'] = $clockOutDateTime;

            $existingRequest = AttendanceRequest::where('employee_id', $employeeId)
                ->where('date', $validatedData['date'])
                ->where('shift_id', $validatedData['shift_id'])
                ->first();

            if (!$existingRequest) {
                if ($request->hasFile('file')) {
                    $validatedData['file'] = $request->file('file')->store('attendance_files', 'public');
                }

                AttendanceRequest::create($validatedData);

                return redirect()
                    ->route('attendance.log')
                    ->with('success', 'Attendance request created successfully.');
            } else {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['message' => 'Attendance request already exists for this date and shift.']);
            }
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => 'Failed to create attendance request: ' . $e->getMessage()]);
        }
    }

    public function get_request(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $attendance_requests = AttendanceRequest::where('employee_id', $employeeId)->get();

        return DataTables::of($attendance_requests)
            ->toJson();
    }
}
