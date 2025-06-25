<?php

namespace App\Domain\BCGMetrics\BLL\BCGMetrics;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\BCGMetrics\DAL\BCGMetrics\BCGMetricsDALInterface;

/**
 * @property BCGMetricsDALInterface DAL
 */
class BCGMetricsBLL extends BaseBLL implements BCGMetricsBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(BCGMetricsDALInterface $bCGMetricsDAL)
    {
        $this->DAL = $bCGMetricsDAL;
    }
}
