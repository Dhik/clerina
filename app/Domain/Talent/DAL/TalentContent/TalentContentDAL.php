<?php

namespace App\Domain\Talent\DAL\TalentContent;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Talent\Models\TalentContent;

/**
 * @property TalentContent model
 */
class TalentContentDAL extends BaseDAL implements TalentContentDALInterface
{
    public function __construct(TalentContent $talentContent)
    {
        $this->model = $talentContent;
    }
}
