<?php

namespace App\Domain\Affiliate\BLL\Affiliate;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Affiliate\DAL\Affiliate\AffiliateDALInterface;

/**
 * @property AffiliateDALInterface DAL
 */
class AffiliateBLL extends BaseBLL implements AffiliateBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(AffiliateDALInterface $affiliateDAL)
    {
        $this->DAL = $affiliateDAL;
    }
}
