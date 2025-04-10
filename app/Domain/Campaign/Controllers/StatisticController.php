<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\Statistic\StatisticBLLInterface;
use App\Domain\Campaign\Job\ScrapJob;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\StatisticRequest;
use App\Domain\Campaign\Service\StatisticCardService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function __construct(
        protected StatisticBLLInterface $statisticBLL,
        protected StatisticCardService $cardService
    ) {}

    /**
     * Update or create statistic
     */
    public function store(CampaignContent $campaignContent, StatisticRequest $request): JsonResponse
    {
        $this->authorize('updateCampaignContent', CampaignContent::class);

        return response()->json($this->statisticBLL->store(
            $campaignContent->campaign_id,
            $campaignContent->id,
            Carbon::now(),
            $request->input('like'),
            $request->input('view'),
            $request->input('comment'),
            $campaignContent->tenant_id,
            null,
            $campaignContent->rate_card
        ));
    }

    /**
     * Refresh statistic
     */
    public function refresh(CampaignContent $campaignContent): JsonResponse
    {
        $this->authorize('updateCampaignContent', CampaignContent::class);

        $result = '';

        if (!is_null($campaignContent->link)) {
            $result = $this->statisticBLL->scrapData(
                $campaignContent->campaign_id,
                $campaignContent->id,
                $campaignContent->channel,
                $campaignContent->link,
                $campaignContent->tenant_id,
                $campaignContent->rate_card
            );
            // Check if views are above 10000 and update is_fyp field
            if ($result && $result['view'] > 10000) {
                $campaignContent->is_fyp = 1;
                $campaignContent->save();
            }
        }

        if (!$result) {
            return response()->json('failed')->setStatusCode(500);
        }

        return response()->json($result);
    }

    public function bulkRefresh(Campaign $campaign): RedirectResponse
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);

        $campaignContents = $campaign->load('campaignContents');
        $successCount = 0;
        $failedCount = 0;

        foreach ($campaignContents->campaignContents as $content) {
            if (!is_null($content->link)) {
                // Small delay between each request to avoid rate limiting
                if ($successCount > 0 || $failedCount > 0) {
                    sleep(3); // Sleep for 3 seconds between requests
                }
                
                try {
                    // Do exactly what the refresh method does
                    $result = $this->statisticBLL->scrapData(
                        $campaign->id,
                        $content->id,
                        $content->channel,
                        $content->link,
                        $content->tenant_id,
                        $content->rate_card
                    );
                    
                    // Check if views are above 10000 and update is_fyp field
                    if ($result && isset($result['view']) && $result['view'] > 10000) {
                        $content->is_fyp = 1;
                        $content->save();
                    }
                    
                    // Count success
                    if ($result) {
                        $successCount++;
                        Log::info("Successfully refreshed content ID: {$content->id}");
                    } else {
                        $failedCount++;
                        Log::error("Failed to refresh content ID: {$content->id}");
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("Exception refreshing content ID: {$content->id}, error: " . $e->getMessage());
                }
            }
        }

        // Log the results
        Log::info("Bulk refresh completed for campaign {$campaign->id}: {$successCount} succeeded, {$failedCount} failed");

        return redirect()->back()->with([
            'alert' => $failedCount > 0 ? 'warning' : 'success',
            'message' => trans('messages.process_completed') . " ({$successCount} " . trans('messages.succeeded') . ", {$failedCount} " . trans('messages.failed') . ")",
        ]);
    }

    /**
     * Store information for card statistic
     */
    public function card(int $campaignId, Request $request): JsonResponse
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);
        return response()->json($this->cardService->card($campaignId, $request));
    }

    /**
     * Get data for chart
     */
    public function chart(int $campaignId, Request $request): JsonResponse
    {

        $this->authorize('viewCampaignContent', CampaignContent::class);
        return response()->json($this->statisticBLL->getChartDataCampaign($campaignId, $request));
    }

    /**
     * Get data for chart detail content
     */
    public function chartDetailContent(int $campaignContentId): JsonResponse
    {

        $this->authorize('viewCampaignContent', CampaignContent::class);

        return response()->json($this->statisticBLL->getChartDataCampaignContent($campaignContentId));
    }
    public function refreshCampaignContentsForCurrentMonth(): RedirectResponse
    {
        // Get the start and end dates for the current month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Find campaigns where the start_date is in the current month
        $campaigns = Campaign::whereBetween('start_date', [$startOfMonth, $endOfMonth])->get();

        foreach ($campaigns as $campaign) {
            $campaignContents = $campaign->campaignContents;

            foreach ($campaignContents as $content) {
                $data = [
                    'campaign_id' => $campaign->id,
                    'campaign_content_id' => $content->id,
                    'channel' => $content->channel,
                    'link' => $content->link,
                    'tenant_id' => $content->tenant_id,
                    'rate_card' => $content->rate_card
                ];

                if (!is_null($content->link)) {
                    ScrapJob::dispatch($data);

                    // Retrieve statistics and update is_fyp if view count is above 10000
                    $statistics = $this->statisticBLL->scrapData(
                        $campaign->id,
                        $content->id,
                        $content->channel,
                        $content->link,
                        $content->tenant_id,
                        $content->rate_card
                    );

                    if ($statistics && $statistics['view'] > 10000) {
                        $content->is_fyp = 1;
                        $content->save();
                    }
                }
            }
        }

        return redirect()->back()->with([
            'alert' => 'success',
            'message' => trans('messages.process_ongoing'),
        ]);
    }
}
