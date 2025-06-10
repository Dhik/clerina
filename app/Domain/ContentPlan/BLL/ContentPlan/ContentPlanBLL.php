<?php

namespace App\Domain\ContentPlan\BLL\ContentPlan;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\ContentPlan\DAL\ContentPlan\ContentPlanDALInterface;

/**
 * @property ContentPlanDALInterface DAL
 */
class ContentPlanBLL extends BaseBLL implements ContentPlanBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(ContentPlanDALInterface $contentPlanDAL)
    {
        $this->DAL = $contentPlanDAL;
    }
}
