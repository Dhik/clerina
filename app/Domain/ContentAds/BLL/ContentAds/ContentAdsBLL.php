<?php

namespace App\Domain\ContentAds\BLL\ContentAds;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\ContentAds\DAL\ContentAds\ContentAdsDALInterface;

/**
 * @property ContentAdsDALInterface DAL
 */
class ContentAdsBLL extends BaseBLL implements ContentAdsBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(ContentAdsDALInterface $contentAdsDAL)
    {
        $this->DAL = $contentAdsDAL;
    }
}
