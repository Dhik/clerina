<?php

namespace App\Domain\Competitor\BLL\Competitor;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Competitor\DAL\Competitor\CompetitorDALInterface;

/**
 * @property CompetitorDALInterface DAL
 */
class CompetitorBLL extends BaseBLL implements CompetitorBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(CompetitorDALInterface $competitorDAL)
    {
        $this->DAL = $competitorDAL;
    }
}
