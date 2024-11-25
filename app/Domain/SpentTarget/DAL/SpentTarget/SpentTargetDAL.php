<?php

namespace App\Domain\SpentTarget\DAL\SpentTarget;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\SpentTarget\Models\SpentTarget;

/**
 * @property SpentTarget model
 */
class SpentTargetDAL extends BaseDAL implements SpentTargetDALInterface
{
    public function __construct(SpentTarget $spentTarget)
    {
        $this->model = $spentTarget;
    }
}
