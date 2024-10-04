<?php

namespace App\Domain\Talent\BLL\Talent;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Talent\DAL\Talent\TalentDALInterface;

/**
 * @property TalentDALInterface DAL
 */
class TalentBLL extends BaseBLL implements TalentBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(TalentDALInterface $talentDAL)
    {
        $this->DAL = $talentDAL;
    }
}
