<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Sales\Controllers\NetProfitController;

class UpdateSheetReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:report-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Sheet Report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(NetProfitController::class);
        $controller->exportCurrentMonthData();

        $this->info('Sheet updated successfully.');
        return Command::SUCCESS;
    }
}
