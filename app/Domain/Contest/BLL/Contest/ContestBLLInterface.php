<?php

namespace App\Domain\Contest\BLL\Contest;

use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Requests\ContestRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;

interface ContestBLLInterface extends BaseBLLInterface
{
    /**
     * Get contest list datatable
     */
    public function getContestDataTable(): Builder;

    /**
     * Create new contest
     */
    public function storeContest(ContestRequest $request): Contest;

    /**
     * Update contest
     */
    public function updateContest(Contest $contest, ContestRequest $request): Contest;

    /**
     * Delete contest
     */
    public function deleteContest(Contest $contest): bool;
}
