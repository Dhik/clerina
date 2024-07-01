<?php

namespace App\Console\Commands;

use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Job\ScrapJob;
use App\Domain\Campaign\Models\CampaignContent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScrapDataCommand extends Command
{
    protected $signature = 'data:scrap {campaignId?}';

    protected $description = 'Command description';

    public function handle(): void
    {
        $campaignId = $this->argument('campaignId');

        $contents = CampaignContent::withoutGlobalScopes()
            ->when(!empty($campaignId), function ($q) use ($campaignId) {
                $q->where('campaign_id', $campaignId);
            })
            ->whereNotNull('link')
            ->whereHas('campaign', function ($q) {
                $currentDate = Carbon::now()->toDateString();

                $q->withoutGlobalScopes()
                    ->whereDate('start_date', '<=', $currentDate)
                    ->whereDate('end_date', '>=', $currentDate);
            })
            ->whereIn('channel', [CampaignContentEnum::TiktokVideo, CampaignContentEnum::InstagramFeed])
            ->chunk(500, function ($contents) {
                foreach ($contents as $content) {
                    $data = [
                        'campaign_id' => $content->campaign_id,
                        'campaign_content_id' => $content->id,
                        'channel' => $content->channel,
                        'link' => $content->link,
                        'tenant_id' => $content->tenant_id,
                        'rate_card' => $content->rate_card
                    ];

                    if (!is_null($content->link)) {
                        ScrapJob::dispatch($data);
                    }
                }
            });
    }
}
