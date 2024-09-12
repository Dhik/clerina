<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Campaign\Controllers\StatisticController;

class RefreshCampaignContents extends Command
{
    protected $signature = 'campaign:refresh-contents';
    protected $description = 'Refresh all campaign contents where the campaigns start date is in the current month';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = app(StatisticController::class);
        $controller->refreshCampaignContentsForCurrentMonth();

        $this->info('Campaign contents refreshed for the current month.');
    }
}
