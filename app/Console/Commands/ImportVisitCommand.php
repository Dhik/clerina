<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Sales\Controllers\SalesController;

class ImportVisitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:visit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports visit data from Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(SalesController::class);
        $controller->importVisitCleora();
        $controller->importVisitAzrina();
        $controller->updateMonthlyVisitData();
        

        $this->info('Visit imported successfully.');
        return Command::SUCCESS;
    }
}
