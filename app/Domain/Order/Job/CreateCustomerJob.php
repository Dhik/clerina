<?php

namespace App\Domain\Order\Job;

use App\Domain\Campaign\BLL\Statistic\StatisticBLL;
use App\Domain\Campaign\BLL\Statistic\StatisticBLLInterface;
use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $data)
    {}

    public function handle(): void
    {
        $customerBLL = app()->make(CustomerBLLInterface::class);
        $customerBLL->createOrUpdateCustomer(
            $this->data['customer_name'],
            $this->data['customer_phone_number'],
            $this->data['tenant_id']
        );
    }
}
