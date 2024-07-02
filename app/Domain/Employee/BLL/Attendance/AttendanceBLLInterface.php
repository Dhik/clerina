<?php

namespace App\Domain\Employee\BLL\Attendance;

use App\DomainUtils\BaseBLL\BaseBLLInterface;

interface AttendanceBLLInterface extends BaseBLLInterface
{
    public function getAllAttendance();
    public function clockIn($user);
    public function clockOut($user);
    public function updateAttendance($id, $data);
    public function deleteAttendance($attendance);
    public function getAttendanceByDate($date);
    public function getOverview($date = null);
}
