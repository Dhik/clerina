<?php

namespace App\Domain\Talent\DAL\Talent;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Talent\Models\Talent;

/**
 * @property Talent model
 */
class TalentDAL extends BaseDAL implements TalentDALInterface
{
    public function __construct(Talent $talent)
    {
        $this->model = $talent;
    }
}
