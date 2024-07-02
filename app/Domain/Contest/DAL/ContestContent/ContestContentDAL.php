<?php

namespace App\Domain\Contest\DAL\ContestContent;

use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Models\ContestContent;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property Contest model
 */
class ContestContentDAL extends BaseDAL implements ContestContentDALInterface
{
    public function __construct(protected ContestContent $content)
    {}

    /**
     * Get contest content datatable
     */
    public function getContestContentDataTable(Contest $contest): Builder
    {
        return $this->content->query()
            ->where('contest_id', $contest->id);
    }

    public function countByContest(Contest $contest): int
    {
        return $this->content->where('contest_id', $contest->id)->count();
    }

    /**
     * Create new contest content
     */
    public function storeContestContent(array $data): ContestContent
    {
        return $this->content->create($data);
    }

    /**
     * Update contest content
     */
    public function updateContestContent(ContestContent $content, array $data): ContestContent
    {
        $content->update($data);
        return $content;
    }

    /**
     * Delete contest content
     */
    public function deleteContestContent(ContestContent $content): void
    {
        $content->delete();
    }
    public function getContestContentByDateRange(Contest $contest, $startDate, $endDate): Builder
    {
        return $this->content->query()
            ->where('contest_id', $contest->id)
            ->whereDate('upload_date', '>=', $startDate)
            ->whereDate('upload_date', '<=', $endDate)
            ->orderBy('upload_date', 'ASC');
    }
}
