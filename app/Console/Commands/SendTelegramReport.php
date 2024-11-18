<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Sales\Controllers\SalesController;

class SendTelegramReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:send-telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily report to Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(SalesController::class);
        $controller->sendMessageCleora();
        $controller->sendMessageAzrina();
        $controller->sendMessageMarketingCleora(); 

        $this->info('Telegram report sent successfully.');
        return Command::SUCCESS;
    }
}
