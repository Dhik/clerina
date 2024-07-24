<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Order\Controllers\OrderController;

class FetchExternalOrders extends Command
{
    protected $signature = 'orders:fetch-external';
    protected $description = 'Fetch external orders and save to the database';
    
    protected $orderController;

    public function __construct(OrderController $orderController)
    {
        parent::__construct();
        $this->orderController = $orderController;
    }

    public function handle()
    {
        $response = $this->orderController->fetchExternalOrders();

        if ($response->getStatusCode() == 200) {
            $this->info('Orders fetched and saved successfully.');
        } else {
            $this->error('Failed to fetch orders.');
        }
    }
}
