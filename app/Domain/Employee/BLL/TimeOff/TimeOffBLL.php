<?php

namespace App\Domain\Employee\BLL\TimeOff;

use App\Domain\Employee\Models\TimeOff;

class TimeOffBLL implements TimeOffBLLInterface
{
    public function getPendingTimeOffs()
    {
        return TimeOff::select('time_offs.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'time_offs.employee_id', '=', 'employees.employee_id')
            ->where('time_offs.status_approval', 'pending')
            ->get();
    }

    public function getApprovedTimeOffs()
    {
        return TimeOff::select('time_offs.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'time_offs.employee_id', '=', 'employees.employee_id')
            ->where('time_offs.status_approval', 'approved')
            ->get();
    }

    public function getRejectedTimeOffs()
    {
        return TimeOff::select('time_offs.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'time_offs.employee_id', '=', 'employees.employee_id')
            ->where('time_offs.status_approval', 'rejected')
            ->get();
    }

    public function updateTimeOffStatus($id, $status)
    {
        $timeOff = TimeOff::find($id);
        if ($timeOff) {
            $timeOff->status_approval = $status;
            $timeOff->save();
            return true;
        }
        return false;
    }
}
