<?php

namespace App\Domain\Employee\BLL\Attendance;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Employee\DAL\Attendance\AttendanceDALInterface;
use App\Domain\Employee\Models\Attendance;
use App\Domain\Employee\Models\Employee;
use Carbon\Carbon;

/**
 * @property AttendanceDALInterface DAL
 */
class AttendanceBLL extends BaseBLL implements AttendanceBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(AttendanceDALInterface $attendanceDAL)
    {
        $this->attendanceDAL = $attendanceDAL;
    }
    public function getAllAttendance()
    {
        return Attendance::all();
    }
    public function clockIn($user)
    {
        $employeeId = $user->employee_id;
        $clockInTime = Carbon::now();
        $employee = Employee::where('employee_id', $employeeId)->first();
        $shiftId = $employee->shift_id;

        Attendance::create([
            'employee_id' => $employeeId,
            'attendance_status' => 'present',
            'clock_in' => $clockInTime,
            'clock_out' => null,
            'timestamp' => $clockInTime,
            'shift_id' => $shiftId,
        ]);

        return true;
    }

    public function clockOut($user)
    {
        $employeeId = $user->employee_id;
        $clockOutTime = Carbon::now();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereNull('clock_out')
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => $clockOutTime,
            ]);
            return true;
        }

        return false;
    }

    public function updateAttendance($id, $data)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($data);
        return $attendance;
    }

    public function deleteAttendance($attendance)
    {
        $attendance->delete();
    }

    public function getAttendanceByDate($date)
    {
        return Attendance::whereDate('created_at', $date)->get();
    }

    public function getOverview($date = null)
    {
        $attendances = $date ? Attendance::whereDate('created_at', $date)->get() : Attendance::all();

        $onTimeCount = $attendances->where('attendance_status', 'present')
            ->where('clock_in', '<=', Carbon::today()->setTime(8, 0))->count();
        $lateClockInCount = $attendances->where('attendance_status', 'present')
            ->where('clock_in', '>', Carbon::today()->setTime(8, 0))->count();
        $earlyClockOutCount = $attendances->where('attendance_status', 'present')
            ->where('clock_out', '<', Carbon::today()->setTime(16, 30))->count();
        $absentCount = $attendances->where('attendance_status', 'absent')->count();
        $noClockInCount = $attendances->whereNull('clock_in')->count();
        $noClockOutCount = $attendances->whereNull('clock_out')->count();
        $invalidCount = 0; // Define your logic for invalid attendance
        $dayOffCount = 0; // Define your logic for day off
        $timeOffCount = 0; // Define your logic for time off

        return [
            'onTimeCount' => $onTimeCount,
            'lateClockInCount' => $lateClockInCount,
            'earlyClockOutCount' => $earlyClockOutCount,
            'absentCount' => $absentCount,
            'noClockInCount' => $noClockInCount,
            'noClockOutCount' => $noClockOutCount,
            'invalidCount' => $invalidCount,
            'dayOffCount' => $dayOffCount,
            'timeOffCount' => $timeOffCount,
        ];
    }
}
