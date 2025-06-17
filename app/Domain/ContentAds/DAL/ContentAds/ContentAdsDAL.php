<?php

namespace App\Domain\ContentAds\DAL\ContentAds;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\ContentAds\Models\ContentAds;

/**
 * @property ContentAds model
 */
class ContentAdsDAL extends BaseDAL implements ContentAdsDALInterface
{
    public function __construct(ContentAds $contentAds)
    {
        $this->model = $contentAds;
    }
}
