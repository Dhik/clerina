<?php

namespace App\Console\Commands;

use App\Domain\Marketing\BLL\Marketing\MarketingBLL;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

/**
 * @property MarketingBLL $marketingBLL
 */
class CreateMarketingCommand extends Command
{
    protected $signature = 'marketing:create';

    protected $description = 'Command description';

    public function __construct(MarketingBLL $marketingBLL)
    {
        parent::__construct();

        $this->marketingBLL = $marketingBLL;
    }

    public function handle(): void
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        $period = CarbonPeriod::create($startDateString, '1 day', $endDateString);

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            foreach ($period as $date) {
                $this->marketingBLL->syncMarketingRecap($date, $tenant->id);
            }
        }
    }
}
