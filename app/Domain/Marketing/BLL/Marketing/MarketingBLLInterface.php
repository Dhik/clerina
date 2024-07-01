<?php

namespace App\Domain\Marketing\BLL\Marketing;

use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Requests\BrandingStoreRequest;
use App\Domain\Marketing\Requests\MarketingStoreRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface MarketingBLLInterface extends BaseBLLInterface
{
    /**
     * Return marketing for DataTable
     */
    public function getMarketingDataTable(Request $request, int $tenantId): Builder;

    /**
     * Create marketing data type branding
     */
    public function createBranding(BrandingStoreRequest $request, int $tenantId): Marketing;

    /**
     * Update marketing data type branding
     */
    public function updateBranding(Marketing $marketing, BrandingStoreRequest $request, int $tenantId): Marketing;

    /**
     * Create marketing data type marketing
     */
    public function createMarketing(MarketingStoreRequest $request, int $tenantId): Marketing;

    /**
     * Update marketing data type marketing
     */
    public function updateMarketing(Marketing $marketing, MarketingStoreRequest $request, int $tenantId): Marketing;

    /**
     * Delete marketing
     */
    public function deleteMarketing(Marketing $marketing, int $tenantId): void;

    /**
     * Import marketing
     *
     * @throws Exception
     */
    public function importMarketing(Request $request, int $tenantId): void;

    /**
     * Create marketing recap
     */
    public function syncMarketingRecap($date, int $tenantId): void;

    /**
     * Retrieves marketing recap information based on the provided request.
     */
    public function getMarketingRecap(Request $request, int $tenantId): array;
}
