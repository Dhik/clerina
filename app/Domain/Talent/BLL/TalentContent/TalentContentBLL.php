<?php

namespace App\Domain\Talent\BLL\TalentContent;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\Domain\Talent\DAL\TalentContent\TalentContentDALInterface;

/**
 * @property TalentContentDALInterface DAL
 */
class TalentContentBLL extends BaseBLL implements TalentContentBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(TalentContentDALInterface $talentContentDAL)
    {
        $this->DAL = $talentContentDAL;
    }
}
