<?php

namespace App\Domain\BCGMetrics\DAL\BCGMetrics;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\BCGMetrics\Models\BCGMetrics;

/**
 * @property BCGMetrics model
 */
class BCGMetricsDAL extends BaseDAL implements BCGMetricsDALInterface
{
    public function __construct(BCGMetrics $bCGMetrics)
    {
        $this->model = $bCGMetrics;
    }
}
