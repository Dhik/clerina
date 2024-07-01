<?php

namespace App\Console\Commands;

use App\Domain\Contest\Job\ScrapJob;
use App\Domain\Contest\Models\ContestContent;
use Illuminate\Console\Command;

class ScrapDataContestCommand extends Command
{
    protected $signature = 'data:scrap-contest';

    protected $description = 'Command description';

    public function handle(): void
    {
        $contestContent = ContestContent::all();

        foreach ($contestContent as $content) {
            ScrapJob::dispatch($content);
        }
    }
}
