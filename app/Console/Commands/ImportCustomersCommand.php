<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Customer\Controllers\CustomerAnalysisController;

class ImportCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports customers data from Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(CustomerAnalysisController::class);
        $controller->importCustomers();

        $this->info('Customers imported successfully.');
        return Command::SUCCESS;
    }
}
