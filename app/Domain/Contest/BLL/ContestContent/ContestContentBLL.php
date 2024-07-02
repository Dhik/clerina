<?php

namespace App\Domain\Contest\BLL\ContestContent;

use App\Domain\Campaign\Service\TiktokScrapperService;
use App\Domain\Contest\BLL\Contest\ContestBLLInterface;
use App\Domain\Contest\DAL\Contest\ContestDALInterface;
use App\Domain\Contest\DAL\ContestContent\ContestContentDALInterface;
use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Models\ContestContent;
use App\Domain\Contest\Requests\ContestContentRequest;
use App\Domain\Contest\Requests\ContestRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

/**
 * @property ContestDALInterface DAL
 */
class ContestContentBLL extends BaseBLL implements ContestContentBLLInterface
{
    public function __construct(
        protected ContestContentDALInterface $contestContentDAL,
        protected TiktokScrapperService $tiktokScrapperService,
    ) {
    }

    /**
     * Get contest content list datatable
     */
    public function getContestContentDataTable(Contest $contest): Builder
    {
        return $this->contestContentDAL->getContestContentDataTable($contest);
    }

    public function syncContest(int $contestId): void
    {
        $contest = Contest::with('contestContent')->find($contestId);

        if (is_null($contest) || $contest->contestContent->isEmpty()) {
            return;
        }

        $contestContent = $contest->contestContent;

        $totalContent = $contestContent->count();
        $totalCreator = $contestContent->groupBy('username')->count();
        $sumRate = $contestContent->sum('rate_total');
        $sumView = $contestContent->sum('view');
        $sumInteraction = $contestContent->sum('interaction');
        $averageInteraction = $totalContent > 0 ? $sumInteraction / $totalContent : 0;

        $preparedData = [
            'used_budget' => $sumRate,
            'last_update' => Carbon::now(),
            'total_content' => $totalContent,
            'total_creator' => $totalCreator,
            'cumulative_views' => $sumView,
            'interaction' => $averageInteraction,
        ];

        $contest->update($preparedData);
    }

    /**
     * Create new contest content
     */
    public function storeContestContent(ContestContentRequest $request): ContestContent
    {
        $data = [
            'contest_id' => $request->input('contest_id'),
            'link' => $request->input('link'),
            'rate' => $request->input('rate'),
        ];

        $content = $this->contestContentDAL->storeContestContent($data);
        $this->syncContest($content->contest_id);

        return $content;
    }

    /**
     * Update contest content
     */
    public function updateContestContent(ContestContent $content, ContestContentRequest $request): ContestContent
    {
        $data = [
            'link' => $request->input('link'),
            'rate' => $request->input('rate'),
        ];

        $content = $this->contestContentDAL->updateContestContent($content, $data);
        $this->syncContest($content->contest_id);

        return $content;
    }

    /**
     * Delete contest
     */
    public function deleteContestContent(ContestContent $content): bool
    {
        $this->contestContentDAL->deleteContestContent($content);
        $this->syncContest($content->contest_id);
        return true;
    }

    public function scrapData(ContestContent $content)
    {
        if (!is_null($content->link)) {
            $data = $this->tiktokScrapperService->getData($content->link);

            if (!is_null($data)) {

                $prepareDataForUpdate = [
                    'view' => $data['view'],
                    'like' => $data['like'],
                    'comment' => $data['comment'],
                    'share' => $data['share'],
                    'interaction' => round(($data['like'] + $data['comment'] + $data['share']) / $data['view'], 4) * 100,
                    'upload_date' => !is_null($data['upload_date']) ? Carbon::createFromTimestamp($data['upload_date']) : null,
                    'duration' => $data['duration'],
                    'username' => $data['username'],
                    'rate_total' => $content->rate * $data['view']
                ];

                $result = $this->contestContentDAL->updateContestContent($content, $prepareDataForUpdate);
                $this->syncContest($result->contest_id);

                return $result;
            }
        }

        return false;
    }

    public function getContestContentRecap(Contest $contest, $filterDates = null): Builder
    {
        $startDateString = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDateString = Carbon::now()->endOfMonth()->format('Y-m-d');

        if (!is_null($filterDates)) {
            [$startDateString, $endDateString] = explode(' - ', $filterDates);
            $startDateString = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDateString = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }

        return $this->contestContentDAL->getContestContentByDateRange($contest, $startDateString, $endDateString);
    }

}
