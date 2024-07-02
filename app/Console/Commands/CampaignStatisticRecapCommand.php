<?php

namespace App\Console\Commands;

use App\Domain\Campaign\Job\CampaignRecapJob;
use App\Domain\Campaign\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CampaignStatisticRecapCommand extends Command
{
    protected $signature = 'statistic:campaign-recap';

    protected $description = 'Recap Statistic for Campaign';

    public function handle(): void
    {
        $currentDate = Carbon::now()->toDateString();

        Campaign::select('id')
            ->withoutGlobalScopes()
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->chunk(500, function ($campaigns) {
                foreach ($campaigns as $campaign) {
                    $data = [
                        'campaign_id' => $campaign->id
                    ];

                    CampaignRecapJob::dispatch($data);
                }
            });
    }
}
