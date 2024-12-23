<?php

namespace App\Domain\Report\DAL\Report;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Report\Models\Report;

/**
 * @property Report model
 */
class ReportDAL extends BaseDAL implements ReportDALInterface
{
    public function __construct(Report $report)
    {
        $this->model = $report;
    }
}
