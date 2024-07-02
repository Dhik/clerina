<?php

namespace App\Domain\Contest\DAL\Contest;

use App\Domain\Contest\Models\Contest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;

interface ContestDALInterface extends BaseDALInterface
{
    /**
     * Get contest datatable
     */
    public function getContestDataTable(): Builder;

    /**
     * Create new contest
     */
    public function storeContest(array $data): Contest;

    /**
     * Update contest
     */
    public function updateContest(Contest $contest, array $data): Contest;

    /**
     * Delete contest
     */
    public function deleteContest(Contest $contest): void;
}
