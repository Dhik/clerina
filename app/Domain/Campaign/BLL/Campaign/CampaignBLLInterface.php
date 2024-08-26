<?php

namespace App\Domain\Campaign\BLL\Campaign;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Requests\CampaignRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface CampaignBLLInterface extends BaseBLLInterface
{
    /**
     * Get campaign list datatable
     */
    public function getCampaignDataTable(Request $request): Builder;

    /**
     * Create new campaign
     */
    public function storeCampaign(CampaignRequest $request): Campaign;

    /**
     * Update campaign
     */
    public function updateCampaign(Campaign $campaign, CampaignRequest $request): Campaign;

    /**
     * Delete campaign
     */
    public function deleteCampaign(Campaign $campaign): bool;

    public function getCampaignSummary(Request $request, int $tenantId): array;
}
