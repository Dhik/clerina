<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected $commands = [
        \App\Console\Commands\ImportOrders::class,
        \App\Console\Commands\ImportCampaignContent::class,
        \App\Console\Commands\RefreshCampaignContents::class,
        \App\Console\Commands\ImportTalentCommand::class,
        \App\Console\Commands\SendTelegramReport::class,
        \App\Console\Commands\ImportFromGoogleSheet::class,
    ];


    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sales:create')->dailyAt('00:01')->withoutOverlapping();
        $schedule->command('marketing:create')->dailyAt('00:02')->withoutOverlapping();
        $schedule->command('data:scrap')->dailyAt('04:00');
        $schedule->command('data:scrap-contest')->dailyAt('05:00');
        $schedule->command('statistic:campaign-recap')->dailyAt('05:30');
        $schedule->command('orders:fetch-external')->cron('0 9,12,17,19,21,3,6 * * *')->timezone('Asia/Jakarta');
        $schedule->command('attendance:populate')->dailyAt('00:05');
        $schedule->command('campaign:refresh-contents')->dailyAt('03:00');
        $schedule->command('report:send-telegram')->dailyAt('15:30');
        $schedule->command('google-sheet:import')->dailyAt('14:30');
        $schedule->command('import:visit')->dailyAt('14:00');

        // $schedule->command('postings:update daily')->daily();
        // $schedule->command('postings:update 3days')->cron('0 0 */3 * *'); // Every 3 days
        // $schedule->command('postings:update weekly')->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
