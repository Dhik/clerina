<?php

namespace App\Domain\Employee\BLL\Overtime;

interface OvertimeBLLInterface
{
    public function getPendingOvertime();
    public function getApprovedOvertime();
    public function getRejectedOvertime();
    public function updateOvertimeStatus($id, $status);
}
