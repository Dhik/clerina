<?php

namespace App\Domain\Campaign\BLL\CampaignContent;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\CampaignContentRequest;
use App\Domain\Campaign\Requests\CampaignUpdateContentRequest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Yajra\DataTables\Utilities\Request;

interface CampaignContentBLLInterface
{
    /**
     * Return campaign content datatable
     */
    public function getCampaignContentDataTable(int $campaignId, Request $request): Builder;

    /**
     * Get Approved KOL
     */
    public function getApprovedKOL(int $campaignId, ?string $search): ?Collection;

    /**
     * Create new campaign content
     */
    public function storeCampaignContent(int $campaignId, CampaignContentRequest $request): CampaignContent;

    /**
     * Update campaign content
     */
    public function updateCampaignContent(CampaignContent $campaignContent, CampaignUpdateContentRequest $request): CampaignContent;

    /**
     * Update FYP campaign content
     */
    public function updateFyp(CampaignContent $campaignContent): CampaignContent;

    /**
     * Update Product deliver campaign content
     */
    public function updateDeliver(CampaignContent $campaignContent): CampaignContent;

    /**
     * Update payment campaign content
     */
    public function updatePay(CampaignContent $campaignContent): CampaignContent;

    /**
     * Import Content
     * @throws Exception
     */
    public function importContent(Request $request, int $tenantId, Campaign $campaign): string;

    /**
     * Delete campaign content
     */
    public function deleteCampaignContent(CampaignContent $campaignContent): void;

    public function getCampaignContentDataTableForRefresh(int $campaignId): Collection;
}
