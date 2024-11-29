<?php

namespace App\Domain\Campaign\BLL\CampaignContent;

use App\Domain\Campaign\DAL\CampaignContent\CampaignContentDALInterface;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\Statistic;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\CampaignContentRequest;
use App\Domain\Campaign\Requests\CampaignUpdateContentRequest;
use App\Domain\Campaign\Service\CampaignImportService;
use App\Domain\Campaign\Service\CampaignImportServiceKOL;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Support\Facades\DB;


class CampaignContentBLL implements CampaignContentBLLInterface
{
    public function __construct(
        protected CampaignContentDALInterface $campaignContentDAL,
        protected CampaignImportService $campaignImportService,
        protected CampaignImportServiceKOL $campaignImportServiceKOL
    )
    {
    }

    /**
     * Return campaign content datatable
     */
    public function getCampaignContentDataTable(int $campaignId, Request $request): Builder
    {
        $query = $this->campaignContentDAL->getCampaignContentDatatable($campaignId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->with(['latestStatistic' => function ($query) use ($endDate) {
                    $query->where('date', '<=', $endDate);
                }])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);
        } else {
            $query->with('latestStatistic');
        }

        if (! is_null($request->input('filterPlatform'))) {
            $query->where('channel', $request->input('filterPlatform'));
        }

        if ($request->input('filterFyp') === 'true') {
            $query->where('is_fyp', 1);
        }

        if ($request->input('filterPayment') === 'true') {
            $query->where('is_paid', 1);
        }

        if ($request->input('filterDelivery') === 'true') {
            $query->where('is_product_deliver', 1);
        }

        return $query;
    }

    /**
     * Get Approved KOL
     */
    public function getApprovedKOL(int $campaignId, ?string $search): ?Collection
    {
        // Fetch the list of collections from the DAL
        $kol = $this->campaignContentDAL->getCampaignKOL($campaignId, $search);

        // Modify the collection to add the 'remaining_slot' key and filter items with remaining_slot > 0
        $kol = $kol->map(function ($item, $key) {
            $usedSlot = $this->campaignContentDAL->countUsedSlot($item['campaign_id'], $item['key_opinion_leader_id']);
            $remainingSlot = $item['total_acc_slot'] - $usedSlot;

            // If remaining_slot is zero, return null to filter it out
            if ($remainingSlot <= 0) {
                return null;
            }

            // Add the 'remaining_slot' key
            $item['remaining_slot'] = $remainingSlot;
            return $item;
        })->filter()->values(); // Filter out null values

        // Ensure consistency in the structure of the returned data
        if ($kol->isEmpty()) {
            return collect(); // Return an empty collection
        }
        // If there's only one item, wrap it in an array
        if ($kol->count() === 1) {
            $kol = [$kol->first()];
        }

        return collect($kol);
    }

    /**
     * Create new campaign content
     */
    public function storeCampaignContent(int $campaignId, CampaignContentRequest $request): CampaignContent
    {
        // Fetch data from the request
        $data = $request->only(['username', 'channel', 'rate_card', 'task_name', 'link', 'product', 'boost_code', 'kode_ads']);

        // Check if the KOL exists
        $existingKOL = KeyOpinionLeader::where('username', $data['username'])->first();

        if ($existingKOL) {
            // If the record exists, update it without modifying average_view and cpm
            $existingKOL->update([
                'channel' => $data['channel'],
                'rate' => $data['rate_card'],
                'created_by' => Auth::user()->id,
                'pic_contact' => Auth::user()->id,
            ]);
            $kol = $existingKOL;
        } else {
            // If the record does not exist, create a new one with average_view and cpm set to 0
            $kol = KeyOpinionLeader::create([
                'username' => $data['username'],
                'channel' => $data['channel'],
                'niche' => '-',
                'average_view' => 0,
                'skin_type' => '-',
                'skin_concern' => '-',
                'content_type' => '-',
                'rate' => $data['rate_card'],
                'cpm' => 0,
                'created_by' => Auth::user()->id,
                'pic_contact' => Auth::user()->id,
            ]);
        }

        // Prepare data for campaign content
        $campaignContentData = [
            'key_opinion_leader_id' => $kol->id,  // Use the $kol instance for ID
            'username' => $data['username'],
            'rate_card' => $data['rate_card'],
            'task_name' => $data['task_name'],
            'link' => $data['link'],
            'product' => $data['product'],
            'channel' => $data['channel'],
            'boost_code' => $data['boost_code'],
            'kode_ads' => $data['kode_ads'],
            'created_by' => Auth::user()->id,
            'campaign_id' => $campaignId,
        ];

        // Store the campaign content
        return $this->campaignContentDAL->storeCampaignContent($campaignContentData);
    }


    /**
     * Update campaign content
     */
    public function updateCampaignContent(CampaignContent $campaignContent, CampaignUpdateContentRequest $request): CampaignContent
    {
        return DB::transaction(function () use ($campaignContent, $request) {
            $data = [
                'rate_card' => $request->input('rate_card'),
                'task_name' => $request->input('task_name'),
                'link' => $request->input('link'),
                'product' => $request->input('product'),
                'channel' => $request->input('channel'),
                'boost_code' => $request->input('boost_code'),
                'kode_ads' => $request->input('kode_ads'),
            ];

            $updatedCampaignContent = $this->campaignContentDAL->updateCampaignContent($campaignContent, $data);

            $statistic = Statistic::where('campaign_id', $campaignContent->campaign_id)
                              ->where('campaign_content_id', $campaignContent->id)
                              ->first();

            if ($statistic) {
                // Update existing statistic
                $statistic->update([
                    'view' => $request->input('views', $statistic->view),
                    'like' => $request->input('likes', $statistic->like),
                    'comment' => $request->input('comments', $statistic->comment),
                ]);
            } else {
                // Create new statistic if it doesn't exist
                $statistic = Statistic::create([
                    'campaign_id' => $campaignContent->campaign_id,
                    'campaign_content_id' => $campaignContent->id,
                    'date' => $campaignContent->upload_date ?? now()->toDateString(),
                    'view' => $request->input('views', 0),
                    'like' => $request->input('likes', 0),
                    'comment' => $request->input('comments', 0),
                    'tenant_id' => Auth::user()->current_tenant_id,
                ]);
            }

            // Calculate and update CPM and engagement
            $totalImpressions = $statistic->view;
            $totalEngagements = $statistic->like + $statistic->comment;
            
            $cpm = $totalImpressions > 0 ? ($updatedCampaignContent->rate_card / $totalImpressions) * 1000 : 0;

            $statistic->update([
                'cpm' => $cpm,
                'engagement' => $totalEngagements,
            ]);

            return $updatedCampaignContent;
        });
    }

    /**
     * Update FYP campaign content
     */
    public function updateFyp(CampaignContent $campaignContent): CampaignContent
    {
        $data = [
            'is_fyp' => !$campaignContent->is_fyp
        ];

        return $this->campaignContentDAL->updateCampaignContent($campaignContent, $data);
    }

    /**
     * Update Product deliver campaign content
     */
    public function updateDeliver(CampaignContent $campaignContent): CampaignContent
    {
        $data = [
            'is_product_deliver' => !$campaignContent->is_product_deliver
        ];

        return $this->campaignContentDAL->updateCampaignContent($campaignContent, $data);
    }

    /**
     * Update payment campaign content
     */
    public function updatePay(CampaignContent $campaignContent): CampaignContent
    {
        $data = [
            'is_paid' => !$campaignContent->is_paid
        ];

        return $this->campaignContentDAL->updateCampaignContent($campaignContent, $data);
    }

    /**
     * Import Content
     * @throws Exception
     */
    public function importContent(Request $request, int $tenantId, Campaign $campaign): string
    {
        return $this->campaignImportService->importContent($request, $tenantId, $campaign);
    }

    public function importContentKOL(Request $request, int $tenantId, Campaign $campaign): string
    {
        return $this->campaignImportServiceKOL->importContent($request, $tenantId, $campaign);
    }

    /**
     * Delete campaign content
     */
    public function deleteCampaignContent(CampaignContent $campaignContent): void
    {
        $this->campaignContentDAL->deleteCampaignContent($campaignContent);
    }

    public function getCampaignContentDataTableForRefresh(int $campaignId): Collection
    {
        return $this->campaignContentDAL->getCampaignContentDataTableForRefresh($campaignId);
    }


}
