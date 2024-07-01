<?php

namespace App\Domain\Employee\BLL\AttendanceRequest;

use App\Domain\Employee\Models\AttendanceRequest;
use App\Domain\Employee\Models\Attendance;
use Carbon\Carbon;

class AttendanceRequestBLL implements AttendanceRequestBLLInterface
{
    public function getPendingRequests()
    {
        return AttendanceRequest::select('attendance_requests.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'attendance_requests.employee_id', '=', 'employees.employee_id')
            ->where('attendance_requests.status_approval', 'pending')
            ->get();
    }

    public function getApprovedRequests()
    {
        return AttendanceRequest::select('attendance_requests.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'attendance_requests.employee_id', '=', 'employees.employee_id')
            ->where('attendance_requests.status_approval', 'approved')
            ->get();
    }

    public function getRejectedRequests()
    {
        return AttendanceRequest::select('attendance_requests.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'attendance_requests.employee_id', '=', 'employees.employee_id')
            ->where('attendance_requests.status_approval', 'rejected')
            ->get();
    }

    public function updateRequestStatus($id, $status)
    {
        $attendanceRequest = AttendanceRequest::find($id);
        if ($attendanceRequest) {
            $attendanceRequest->status_approval = $status;
            $attendanceRequest->save();

            if ($status == 'approved') {
                $attendance = Attendance::where('employee_id', $attendanceRequest->employee_id)
                    ->whereDate('created_at', $attendanceRequest->date)
                    ->first();

                if ($attendance) {
                    $attendance->clock_in = $attendanceRequest->clock_in;
                    $attendance->clock_out = $attendanceRequest->clock_out;
                    $attendance->save();
                }
            }
            return true;
        }
        return false;
    }
}
