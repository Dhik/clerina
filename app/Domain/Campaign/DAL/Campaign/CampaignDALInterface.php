<?php

namespace App\Domain\Campaign\DAL\Campaign;

use App\Domain\Campaign\Models\Campaign;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CampaignDALInterface extends BaseDALInterface
{
    /**
     * Get campaign list datatable
     */
    public function getCampaignDataTable(): Builder;

    /**
     * Create new campaign
     */
    public function storeCampaign(array $data): Campaign;

    /**
     * Update campaign
     */
    public function updateCampaign(Campaign $campaign, array $data): Campaign;

    /**
     * Delete Campaign
     */
    public function deleteCampaign(Campaign $campaign): void;

    /**
     * Check campaign offer
     */
    public function checkOffer(Campaign $campaign);

    /**
     * Check campaign content
     */
    public function checkCampaignContent(Campaign $campaign);

    public function getCampaignsByDateRange($startDate, $endDate, int $tenantId): Collection;
}
