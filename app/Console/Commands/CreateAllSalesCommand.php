<?php

namespace App\Console\Commands;

use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Sales\Models\Sales;
use Illuminate\Console\Command;

/**
 * @property SalesBLLInterface $salesBLL
 */
class CreateAllSalesCommand extends Command
{
    protected $signature = 'sales:create-all';

    protected $description = 'Command description';

    public function __construct(SalesBLLInterface $salesBLL)
    {
        parent::__construct();

        $this->salesBLL = $salesBLL;
    }

    public function handle(): void
    {
        $sales = Sales::all();

        foreach ($sales as $sale) {
            $this->salesBLL->createSales($sale->date);
        }
    }
}
