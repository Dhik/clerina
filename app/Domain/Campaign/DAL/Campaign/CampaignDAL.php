<?php

namespace App\Domain\Campaign\DAL\Campaign;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\Offer;
use App\DomainUtils\BaseDAL\BaseDAL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;


class CampaignDAL extends BaseDAL implements CampaignDALInterface
{
    public function __construct(
        protected Campaign $campaign,
        protected Offer $offer,
        protected CampaignContent $campaignContent
    ) {}

    /**
     * Get campaign list datatable
     */
    public function getCampaignDataTable(): Builder
    {
        return $this->campaign->query()->with('createdBy');
    }

    /**
     * Create new campaign
     */
    public function storeCampaign(array $data): Campaign
    {
        return $this->campaign->create($data);
    }

    /**
     * Update campaign
     */
    public function updateCampaign(Campaign $campaign, array $data): Campaign
    {
        $campaign->update($data);
        return $campaign;
    }

    /**
     * Delete Campaign
     */
    public function deleteCampaign(Campaign $campaign): void
    {
        $campaign->delete();
    }

    /**
     * Check campaign offer
     */
    public function checkOffer(Campaign $campaign)
    {
        return $this->offer->where('campaign_id', $campaign->id)->first();
    }

    /**
     * Check campaign content
     */
    public function checkCampaignContent(Campaign $campaign)
    {
        return $this->campaignContent->where('campaign_id', $campaign->id)->first();
    }
    public function getCampaignsByDateRange($startDate, $endDate, int $tenantId): Collection
    {
        $query = $this->campaign->where('tenant_id', $tenantId);

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }

        return $query->get();
    }


}
