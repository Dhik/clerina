<?php

namespace App\Domain\Competitor\DAL\Competitor;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Competitor\Models\Competitor;

/**
 * @property Competitor model
 */
class CompetitorDAL extends BaseDAL implements CompetitorDALInterface
{
    public function __construct(Competitor $competitor)
    {
        $this->model = $competitor;
    }
}
