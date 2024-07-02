<?php

namespace App\Domain\Contest\BLL\Contest;

use App\Domain\Contest\DAL\Contest\ContestDALInterface;
use App\Domain\Contest\DAL\ContestContent\ContestContentDALInterface;
use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Requests\ContestRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property ContestDALInterface DAL
 */
class ContestBLL extends BaseBLL implements ContestBLLInterface
{
    public function __construct(
        protected ContestDALInterface $contestDAL,
        protected ContestContentDALInterface $contentDAL
    ) {
    }

    /**
     * Get contest list datatable
     */
    public function getContestDataTable(): Builder
    {
        return $this->contestDAL->getContestDataTable();
    }

    /**
     * Create new contest
     */
    public function storeContest(ContestRequest $request): Contest
    {
        $data = [
            'title' => $request->input('title'),
            'budget' => $request->input('budget')
        ];

        return $this->contestDAL->storeContest($data);
    }

    /**
     * Update contest
     */
    public function updateContest(Contest $contest, ContestRequest $request): Contest
    {
        $data = [
            'title' => $request->input('title'),
            'budget' => $request->input('budget')
        ];

        return $this->contestDAL->updateContest($contest, $data);
    }

    /**
     * Delete contest
     */
    public function deleteContest(Contest $contest): bool
    {
        $checkContent = $this->contentDAL->countByContest($contest);

        if ($checkContent > 0) {
            return false;
        }

        $this->contestDAL->deleteContest($contest);

        return true;
    }
}
