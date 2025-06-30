<?php

namespace App\Domain\KPIEmployee\DAL\KPIEmployee;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\KPIEmployee\Models\KPIEmployee;

/**
 * @property KPIEmployee model
 */
class KPIEmployeeDAL extends BaseDAL implements KPIEmployeeDALInterface
{
    public function __construct(KPIEmployee $kPIEmployee)
    {
        $this->model = $kPIEmployee;
    }
}
