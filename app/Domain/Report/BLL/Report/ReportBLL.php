<?php

namespace App\Domain\Report\BLL\Report;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Report\DAL\Report\ReportDALInterface;

/**
 * @property ReportDALInterface DAL
 */
class ReportBLL extends BaseBLL implements ReportBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(ReportDALInterface $reportDAL)
    {
        $this->DAL = $reportDAL;
    }
}
