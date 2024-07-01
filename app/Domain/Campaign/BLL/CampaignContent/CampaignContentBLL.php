<?php

namespace App\Domain\Campaign\BLL\CampaignContent;

use App\Domain\Campaign\DAL\CampaignContent\CampaignContentDALInterface;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\CampaignContentRequest;
use App\Domain\Campaign\Requests\CampaignUpdateContentRequest;
use App\Domain\Campaign\Service\CampaignImportService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Utilities\Request;

class CampaignContentBLL implements CampaignContentBLLInterface
{
    public function __construct(
        protected CampaignContentDALInterface $campaignContentDAL,
        protected CampaignImportService $campaignImportService
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
        $data = [
            'key_opinion_leader_id' => $request->input('key_opinion_leader_id'),
            'rate_card' => $request->input('rate_card'),
            'task_name' => $request->input('task_name'),
            'link' => $request->input('link'),
            'product' => $request->input('product'),
            'channel' => $request->input('channel'),
            'boost_code' => $request->input('boost_code'),
            'created_by' => Auth::user()->id,
            'campaign_id' => $campaignId
        ];

        return $this->campaignContentDAL->storeCampaignContent($data);
    }

    /**
     * Update campaign content
     */
    public function updateCampaignContent(CampaignContent $campaignContent, CampaignUpdateContentRequest $request): CampaignContent
    {
        $data = [
            'rate_card' => $request->input('rate_card'),
            'task_name' => $request->input('task_name'),
            'link' => $request->input('link'),
            'product' => $request->input('product'),
            'channel' => $request->input('channel'),
            'boost_code' => $request->input('boost_code')
        ];

        return $this->campaignContentDAL->updateCampaignContent($campaignContent, $data);
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

    /**
     * Delete campaign content
     */
    public function deleteCampaignContent(CampaignContent $campaignContent): void
    {
        $this->campaignContentDAL->deleteCampaignContent($campaignContent);
    }

}
