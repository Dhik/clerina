<?php

namespace App\Console\Commands;

use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

/**
 * @property SalesBLLInterface $salesBLL
 */
class CreateSalesCommand extends Command
{
    protected $signature = 'sales:create';

    protected $description = 'Command description';

    public function __construct(
        SalesBLLInterface $salesBLL
    ) {
        parent::__construct();

        $this->salesBLL = $salesBLL;
    }

    public function handle(): void
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        $period = CarbonPeriod::create($startDateString, '1 day', $endDateString);

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            foreach ($period as $date) {
                $this->salesBLL->createSales($date, $tenant->id);
            }
        }
    }
}
