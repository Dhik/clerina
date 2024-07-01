<?php

namespace App\Domain\Employee\BLL\Overtime;

use App\Domain\Employee\Models\Overtime;

class OvertimeBLL implements OvertimeBLLInterface
{
    public function getPendingOvertime()
    {
        return Overtime::select('overtimes.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.employee_id')
            ->where('overtimes.status_approval', 'pending')
            ->get();
    }

    public function getApprovedOvertime()
    {
        return Overtime::select('overtimes.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.employee_id')
            ->where('overtimes.status_approval', 'approved')
            ->get();
    }

    public function getRejectedOvertime()
    {
        return Overtime::select('overtimes.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.employee_id')
            ->where('overtimes.status_approval', 'rejected')
            ->get();
    }

    public function updateOvertimeStatus($id, $status)
    {
        $overtime = Overtime::find($id);
        if ($overtime) {
            $overtime->status_approval = $status;
            $overtime->save();
            return true;
        }
        return false;
    }
}
