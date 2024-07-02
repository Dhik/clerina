<?php

namespace App\Domain\Campaign\BLL\Statistic;

use App\Domain\Campaign\Models\Statistic;
use Illuminate\Http\Request;

interface StatisticBLLInterface
{
    /**
     * Update or crate statistic
     */
    public function store(
        int $campaignId,
        int $campaignContentId,
        string $date,
        ?int $like,
        ?int $view,
        ?int $comment,
        int $tenantId,
        ?string $uploadDate = null,
        ?int $rateCard = 0
    ): Statistic;

    public function scrapData(int $campaignId, int $campaignContentId, string $channel, string $link, int $tenantId, int $rateCard): Statistic|bool;

    /**
     * Get data for chart
     */
    public function getChartDataCampaign(int $campaignId, Request $request);

    /**
     * Get data for chart detail content
     */
    public function getChartDataCampaignContent(int $campaignContentId);
}
