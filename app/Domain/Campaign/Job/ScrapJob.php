<?php

namespace App\Domain\Campaign\Job;

use App\Domain\Campaign\BLL\Statistic\StatisticBLL;
use App\Domain\Campaign\Enum\CampaignContentEnum;
use App\Domain\Campaign\Models\CampaignContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     * Uses exponential backoff: 30s, 90s, 270s
     *
     * @var array
     */
    public $backoff = [30, 90, 270];

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct(protected array $data)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Add job tracing
        Log::info('Processing ScrapJob for campaign content: ' . $this->data['campaign_content_id']);
        
        try {
            // Get the campaign content to ensure it still exists and hasn't been updated
            $campaignContent = CampaignContent::find($this->data['campaign_content_id']);
            
            if (!$campaignContent || $campaignContent->link !== $this->data['link']) {
                Log::warning('Campaign content not found or link changed. Skipping job for content ID: ' . $this->data['campaign_content_id']);
                return;
            }
            
            // Get the StatisticBLL service
            $statisticBLL = app()->make(StatisticBLL::class);
            
            // Perform the scraping operation
            $result = $statisticBLL->scrapData(
                $this->data['campaign_id'],
                $this->data['campaign_content_id'],
                $this->data['channel'],
                $this->data['link'],
                $this->data['tenant_id'],
                $this->data['rate_card']
            );
            
            // Update is_fyp flag if views are above threshold
            if ($result && isset($result['view']) && $result['view'] > 10000) {
                $campaignContent->is_fyp = 1;
                $campaignContent->save();
                
                Log::info('Updated is_fyp flag for content ID: ' . $this->data['campaign_content_id'] . 
                          ' with view count: ' . $result['view']);
            }
            
            // Log success
            Log::info('Successfully processed ScrapJob for campaign content: ' . $this->data['campaign_content_id']);
            
        } catch (\Exception $e) {
            Log::error('Error processing ScrapJob for content ID: ' . $this->data['campaign_content_id'] . 
                      ' - ' . $e->getMessage());
            
            // Let Laravel retry based on $tries and $backoff properties
            throw $e;
        }
    }

    /**
     * The job failed to process.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception): void
    {
        Log::error('ScrapJob finally failed after ' . $this->tries . ' attempts for campaign content ID: ' . 
                  $this->data['campaign_content_id'] . ' with error: ' . $exception->getMessage());
    }
}