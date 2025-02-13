<?php

namespace App\Domain\AffiliateTalent\DAL\AffiliateTalent;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\AffiliateTalent\Models\AffiliateTalent;

/**
 * @property AffiliateTalent model
 */
class AffiliateTalentDAL extends BaseDAL implements AffiliateTalentDALInterface
{
    public function __construct(AffiliateTalent $affiliateTalent)
    {
        $this->model = $affiliateTalent;
    }
}
