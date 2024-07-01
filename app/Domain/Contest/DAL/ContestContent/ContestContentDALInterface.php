<?php

namespace App\Domain\Contest\DAL\ContestContent;

use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Models\ContestContent;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;

interface ContestContentDALInterface extends BaseDALInterface
{
    /**
     * Get contest content datatable
     */
    public function getContestContentDataTable(Contest $contest): Builder;

    public function countByContest(Contest $contest): int;

    /**
     * Create new contest content
     */
    public function storeContestContent(array $data): ContestContent;

    /**
     * Update contest content
     */
    public function updateContestContent(ContestContent $content, array $data): ContestContent;

    /**
     * Delete contest content
     */
    public function deleteContestContent(ContestContent $content): void;

    public function getContestContentByDateRange(Contest $contest, $startDate, $endDate): Builder;
}
