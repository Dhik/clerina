<?php

namespace App\Domain\Campaign\Job;

use App\Domain\Campaign\BLL\Statistic\StatisticBLL;
use App\Domain\Campaign\BLL\Statistic\StatisticBLLInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $data)
    {}

    public function handle(): void
    {
        $statisticBLL = app()->make(StatisticBLL::class);
        $statisticBLL->scrapData(
            $this->data['campaign_id'],
            $this->data['campaign_content_id'],
            $this->data['channel'],
            $this->data['link'],
            $this->data['tenant_id'],
            $this->data['rate_card']
        );
    }
}
