<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Sales\Controllers\SalesController;

class ImportFromGoogleSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-sheet:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from Google Sheet into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(SalesController::class);
        $controller->importFromGoogleSheet();
        $controller->updateMonthlyAdSpentData();

        $this->info('Data imported successfully.');
        return Command::SUCCESS;
    }
}
