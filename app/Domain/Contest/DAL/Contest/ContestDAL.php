<?php

namespace App\Domain\Contest\DAL\Contest;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Contest\Models\Contest;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property Contest model
 */
class ContestDAL extends BaseDAL implements ContestDALInterface
{
    public function __construct(protected Contest $contest)
    {}

    /**
     * Get contest datatable
     */
    public function getContestDataTable(): Builder
    {
        return $this->contest->query();
    }

    /**
     * Create new contest
     */
    public function storeContest(array $data): Contest
    {
        return $this->contest->create($data);
    }

    /**
     * Update contest
     */
    public function updateContest(Contest $contest, array $data): Contest
    {
        $contest->update($data);
        return $contest;
    }

    /**
     * Delete contest
     */
    public function deleteContest(Contest $contest): void
    {
        $contest->delete();
    }
}
