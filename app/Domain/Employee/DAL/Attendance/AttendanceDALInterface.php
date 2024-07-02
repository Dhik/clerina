<?php

namespace App\Domain\Employee\DAL\Attendance;

use App\DomainUtils\BaseDAL\BaseDALInterface;

interface AttendanceDALInterface extends BaseDALInterface
{
    public function getAttendanceDataTable(): Builder;
}
