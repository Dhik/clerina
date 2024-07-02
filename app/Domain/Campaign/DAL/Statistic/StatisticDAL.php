<?php

namespace App\Domain\Campaign\DAL\Statistic;

use App\Domain\Campaign\Models\Statistic;
use Illuminate\Http\Request;

class StatisticDAL implements StatisticDALInterface
{
    public function __construct(protected Statistic $statistic)
    {
    }

    /**
     * Update or create statistic
     */
    public function store(array $data): Statistic
    {
        return $this->statistic->updateOrCreate([
                'date' => $data['date'],
                'campaign_id' => $data['campaign_id'],
                'campaign_content_id' => $data['campaign_content_id'],
                'tenant_id' => $data['tenant_id']
            ],[
                'view' => $data['view'] ?? 0,
                'like' => $data['like'] ?? 0,
                'saved' => $data['saved'] ?? 0,
                'comment' => $data['comment'] ?? 0,
                'cpm' => $data['cpm'] ?? 0
        ]);
    }

    /**
     * Get data for chart
     */
    public function getChartDataCampaign(int $campaignId)
    {
        return Statistic::query()->selectRaw('date, SUM(view) as total_view, SUM(CASE WHEN `like` < 0 THEN ABS(`like`) ELSE `like` END) as total_like, SUM(comment) as total_comment')
            ->where('campaign_id', $campaignId)
            ->groupBy('date');
    }

    /**
     * Get data for chart detail content
     */
    public function getChartDataCampaignContent(int $campaignContentId)
    {
        return Statistic::select('date', 'like', 'comment', 'view')
            ->where('campaign_content_id', $campaignContentId)
            ->get();
    }
}
