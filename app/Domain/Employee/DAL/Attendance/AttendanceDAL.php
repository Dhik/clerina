<?php

namespace App\Domain\Employee\DAL\Attendance;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Employee\Models\Attendance;

/**
 * @property Attendance model
 */
class AttendanceDAL extends BaseDAL implements AttendanceDALInterface
{
    public function __construct(
        protected Attendance $attendance,
    )
    {}
    public function getAttendanceDataTable(): Builder
    {
        return $this->attendance->query()
        ->where('employee_id', 1);
    }
}
