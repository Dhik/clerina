<?php

namespace App\Domain\Affiliate\DAL\Affiliate;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Affiliate\Models\Affiliate;

/**
 * @property Affiliate model
 */
class AffiliateDAL extends BaseDAL implements AffiliateDALInterface
{
    public function __construct(Affiliate $affiliate)
    {
        $this->model = $affiliate;
    }
}
