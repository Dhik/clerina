<?php

namespace App\Domain\Campaign\Job;

use App\Domain\Campaign\Service\StatisticCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CampaignRecapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $data)
    {
    }

    public function handle(): void
    {
        $statisticCardService = app()->make(StatisticCardService::class);
        $statisticCardService->recapStatisticCampaign($this->data['campaign_id']);
    }
}
