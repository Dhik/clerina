<?php

namespace App\Domain\ContentPlan\DAL\ContentPlan;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\ContentPlan\Models\ContentPlan;

/**
 * @property ContentPlan model
 */
class ContentPlanDAL extends BaseDAL implements ContentPlanDALInterface
{
    public function __construct(ContentPlan $contentPlan)
    {
        $this->model = $contentPlan;
    }
}
