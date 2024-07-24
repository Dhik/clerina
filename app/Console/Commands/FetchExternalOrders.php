<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Order\Controllers\OrderController;

class FetchExternalOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:fetch-external';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch external orders and save to the database';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $orderController = new OrderController();
        $response = $orderController->fetchExternalOrders();

        if ($response->getStatusCode() == 200) {
            $this->info('Orders fetched and saved successfully.');
        } else {
            $this->error('Failed to fetch orders.');
        }
    }
}
