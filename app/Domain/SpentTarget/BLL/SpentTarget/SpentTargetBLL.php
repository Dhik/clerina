<?php

namespace App\Domain\SpentTarget\BLL\SpentTarget;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\SpentTarget\DAL\SpentTarget\SpentTargetDALInterface;

/**
 * @property SpentTargetDALInterface DAL
 */
class SpentTargetBLL extends BaseBLL implements SpentTargetBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(SpentTargetDALInterface $spentTargetDAL)
    {
        $this->DAL = $spentTargetDAL;
    }
}
