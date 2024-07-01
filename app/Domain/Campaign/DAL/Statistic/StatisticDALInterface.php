<?php

namespace App\Domain\Campaign\DAL\Statistic;

use App\Domain\Campaign\Models\Statistic;
use Illuminate\Http\Request;

interface StatisticDALInterface
{
    /**
     * Update or create statistic
     */
    public function store(array $data): Statistic;

    /**
     * Get data for chart
     */
    public function getChartDataCampaign(int $campaignId);

    /**
     * Get data for chart detail content
     */
    public function getChartDataCampaignContent(int $campaignContentId);
}
