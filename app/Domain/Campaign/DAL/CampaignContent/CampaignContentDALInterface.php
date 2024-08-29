<?php

namespace App\Domain\Campaign\DAL\CampaignContent;

use App\Domain\Campaign\Models\CampaignContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface CampaignContentDALInterface
{
    /**
     * Return campaign content datatable
     */
    public function getCampaignContentDatatable(int $campaignId): Builder;

    /**
     * Get list approved KOL on campaign
     */
    public function getCampaignKOL(int $campaignId, ?string $search): ?Collection;

    /**
     * Count user slot KOL on Campaign
     */
    public function countUsedSlot(int $campaignId, int $kolId): int;

    /**
     * Create campaign content
     */
    public function storeCampaignContent(array $data): CampaignContent;

    /**
     * Update campaign content
     */
    public function updateCampaignContent(CampaignContent $campaignContent, array $data): CampaignContent;

    /**
     * Update upload date
     */
    public function updateUploadDate(int $campaignContentId, string $date): void;

    /**
     * Delete campaign content and statistic
     */
    public function deleteCampaignContent(CampaignContent $campaignContent): void;

    public function getCampaignContentDataTableForRefresh(int $campaignId): Collection;
}
