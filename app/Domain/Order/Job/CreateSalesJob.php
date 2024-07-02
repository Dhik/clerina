<?php

namespace App\Domain\Order\Job;

use App\Domain\Campaign\BLL\Statistic\StatisticBLL;
use App\Domain\Campaign\BLL\Statistic\StatisticBLLInterface;
use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected $date, protected $tenant_id)
    {}

    public function handle(): void
    {
        $salesBLL = app()->make(SalesBLLInterface::class);
        $salesBLL->createSales(
            $this->date,
            $this->tenant_id
        );
    }
}
