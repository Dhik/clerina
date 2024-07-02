<?php

namespace App\Domain\Contest\Job;

use App\Domain\Contest\BLL\ContestContent\ContestContentBLLInterface;
use App\Domain\Contest\Models\ContestContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ContestContent $contestContent)
    {
    }

    public function handle(): void
    {
        $contestContentBLL = app()->make(ContestContentBLLInterface::class);
        $contestContentBLL->scrapData($this->contestContent);
    }
}
