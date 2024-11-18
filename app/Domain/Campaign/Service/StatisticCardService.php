<?php

namespace App\Domain\Campaign\Service;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\Statistic;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticCardService
{
    public function card(int $campaignId, Request $request): array
    {
        $allCampaignContents = CampaignContent::where('campaign_id', $campaignId)->get();
        $startDate = null;
        $endDate = null;

        if ($request->input('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->endOfDay();
        }

        $statistics = Statistic::where('campaign_id', $campaignId)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('campaign_content_id');

        $totalView = 0;

        if ($startDate && $endDate) {
            $sumStartDate = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $startDate)
                ->sum('view');
            $sumEndDate = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $endDate)
                ->sum('view');
            $totalView = $sumEndDate - $sumStartDate;
            $sumStartDateLikes = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $startDate)
                ->sum('like');
            $sumEndDateLikes = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $endDate)
                ->sum('like');
            $totalLike = $sumEndDateLikes - $sumStartDateLikes;
            $sumStartDateComments = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $startDate)
                ->sum('comment');
            $sumEndDateComments = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $endDate)
                ->sum('comment');
            $totalComment = $sumEndDateComments - $sumStartDateComments;
        } else {
            $latestDate = Statistic::where('campaign_id', $campaignId)
                ->max('date');
            $totalView = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $latestDate)
                ->sum('view');
            $totalLike = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $latestDate)
                ->sum('like');
            $totalComment = Statistic::where('campaign_id', $campaignId)
                ->whereDate('date', $latestDate)
                ->sum('comment');
        }

        $totals = [
            'totalView' => $totalView,
            'totalLike' => $totalLike,
            'totalComment' => $totalComment,
            'totalExpense' => $allCampaignContents->sum('rate_card'),
            'cpm' => $totalView > 0 
                ? ($allCampaignContents->sum('rate_card') / $totalView) * 1000 
                : 0,
        ];

        $campaignContents = $this->fetchCampaignContents($campaignId, $request);
        $groupedData = $this->groupDataByKeyOpinionLeader($campaignContents);
        $topData = $this->sortDataByCriteria($groupedData);
        $groupedProducts = $this->groupDataByProduct($campaignContents);
        $topProducts = $this->getTopProducts($groupedProducts);

        return $this->constructTotalData($allCampaignContents, $totals, $topData, $topProducts);
    }




    public function recapStatisticCampaign(int $campaignId): void
    {
        $emptyRequest = new Request();

        $campaignContents = $this->fetchCampaignContents($campaignId, $emptyRequest);
        $totals = $this->calculateTotals($campaignContents);

        $data = [
            'view' => $totals['totalView'],
            'like' => $totals['totalLike'],
            'comment' => $totals['totalComment'],
            'total_expense' => $totals['totalExpense'],
            'achievement' => $totals['totalExpense'] === 0 ? 0 : $totals['totalView'] / $totals['totalExpense'],
            'cpm' => $totals['cpm'],
            'total_content' => $campaignContents->count(),
            'total_influencer' => $campaignContents->pluck('username')->unique()->count(),
        ];

        $campaign = Campaign::withoutGlobalScopes()->where('id', $campaignId)->first();

        if (!is_null($campaign)) {
            $campaign->update($data);
        }
    }

    protected function fetchCampaignContents(int $campaignId, Request $request)
    {
        $query = CampaignContent::select('id','username', 'campaign_id', 'key_opinion_leader_id', 'rate_card', 'product')
            ->with(['latestStatistic' => function ($query) {
                $query->select('id', 'campaign_content_id', 'date', 'view', 'like', 'comment', 'cpm', 'engagement');
            }, 'keyOpinionLeader' => function ($query) {
                $query->select('id', 'username');
            }])
            ->where('campaign_id', $campaignId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->with(['latestStatistic' => function ($query) use ($endDate) {
                    $query->where('date', '<=', $endDate);
                }])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);;
        } else {
            $query->with('latestStatistic');
        }

        return $query->withoutGlobalScopes()->get();
    }

    protected function calculateTotals($campaignContents): array
    {
        $totalView = $campaignContents->sum(fn ($content) => $content->latestStatistic->view ?? 0);
        $totalLike = $campaignContents->sum(fn ($content) => $content->latestStatistic->like ?? 0);
        $totalComment = $campaignContents->sum(fn ($content) => $content->latestStatistic->comment ?? 0);
        $totalEngagement = $campaignContents->sum(fn ($content) => $content->latestStatistic->engagement ?? 0);
        $totalExpense = $campaignContents->sum('rate_card');
        $cpm = $this->calculateCPM($totalView, $totalExpense);

        return compact('totalView', 'totalLike', 'totalComment', 'totalEngagement', 'totalExpense', 'cpm');
    }

    protected function groupDataByKeyOpinionLeader($campaignContents): mixed
    {
        return $campaignContents->groupBy('username')->map(function ($items) {

            $keyOpinionLeader = KeyOpinionLeader::where('username', $items->first()->username)->first();
            return [
                'id' => $keyOpinionLeader->id ?? null,
                'key_opinion_leader_name' => $items->first()->username ?? '',
                'view' => $items->sum(fn($item) => $item->latestStatistic->view ?? 0),
                'like' => $items->sum(fn($item) => $item->latestStatistic->like ?? 0),
                'comment' => $items->sum(fn($item) => $item->latestStatistic->comment ?? 0),
                'engagement' => $items->sum(fn($item) => $item->latestStatistic->engagement ?? 0),
            ];
        });
    }

    protected function sortDataByCriteria($groupedData): array
    {
        $orderBy = ['like', 'comment', 'view', 'engagement'];
        $topData = [];
        foreach ($orderBy as $criteria) {
            $sortedData = $groupedData->sortByDesc($criteria)->take(5);
            $topData[$criteria] = $sortedData->values()->all();
        }
        return $topData;
    }

    protected function groupDataByProduct($campaignContents): mixed
    {
        return $campaignContents->groupBy('product')->map(function ($products) {
            $sumSpend = $products->sum('rate_card');
            $sumViews = $products->sum('latestStatistic.view');
            $totalContent = $products->count();
            $totalEngagement = $products->sum('latestStatistic.engagement');

            return [
                'product' => $products->first()->product,
                'total_engagement' => $totalEngagement,
                'total_views' => number_format($sumViews, 0, ',', '.'),
                'total_spend' => number_format($sumSpend, 0, ',', '.'),
                'total_content' => number_format($totalContent, 0, ',', '.'),
                'cpm' => $sumViews === 0 ? 0 : number_format($sumSpend / $sumViews * 1000, 2, ',', '.'),
                'target' => $sumSpend === 0 ? 0 : number_format($sumViews / $sumSpend * 100, 2, ',', '.') . '%',
            ];
        });
    }

    protected function getTopProducts($groupedProducts): mixed
    {
        return $groupedProducts->sortByDesc('total_engagement')->take(5)->values()->all();
    }

    protected function constructTotalData($campaignContents, $totals, $topData, $topProducts): array
    {
        return [
            'view' => number_format($totals['totalView'], 0, ',', '.'),
            'like' => number_format($totals['totalLike'], 0, ',', '.'),
            'comment' => number_format($totals['totalComment'], 0, ',', '.'),
            'total_influencer' => number_format($campaignContents->pluck('username')->unique()->count(), 0, ',', '.'),
            'total_content' => number_format($campaignContents->count(), 0, ',', '.'),
            'total_expense' => number_format($totals['totalExpense'], 0, ',', '.'),
            'achievement' => $totals['totalExpense'] === 0 ? 0 : number_format($totals['totalView'] / $totals['totalExpense'] * 100, 2, ',', '.') . '%',
            'cpm' => number_format($totals['cpm'], 2, ',', '.'),
            'engagement_rate' => $totals['totalView'] === 0 ? 0 : number_format(($totals['totalLike']+$totals['totalComment']) / $totals['totalView'] * 100, 2, ',', '.') . '%',
            'top_likes' => $topData['like'],
            'top_comment' => $topData['comment'],
            'top_view' => $topData['view'],
            'top_engagement' => $topData['engagement'],
            'top_product' => $topProducts,
        ];
    }

    protected function calculateCPM($view, $rate): float|int
    {
        return $view === 0 ? 0 : ($rate / $view) * 1000;
    }

}
