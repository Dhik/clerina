<?php

namespace App\Domain\KPIEmployee\BLL\KPIEmployee;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\KPIEmployee\DAL\KPIEmployee\KPIEmployeeDALInterface;

/**
 * @property KPIEmployeeDALInterface DAL
 */
class KPIEmployeeBLL extends BaseBLL implements KPIEmployeeBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(KPIEmployeeDALInterface $kPIEmployeeDAL)
    {
        $this->DAL = $kPIEmployeeDAL;
    }
}
