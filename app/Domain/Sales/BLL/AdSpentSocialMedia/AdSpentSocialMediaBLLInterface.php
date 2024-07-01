<?php

namespace App\Domain\Sales\BLL\AdSpentSocialMedia;

use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface AdSpentSocialMediaBLLInterface extends BaseBLLInterface
{
    /**
     * Return AdSpentSocialMedia data for DataTable
     */
    public function getAdSpentSocialMediaDataTable(Request $request, int $tenantId): Builder;

    /**
     * Retrieves AdSpent social media recap information based on the provided request.
     */
    public function getAdSpentSocialMediaRecap(Request $request, int $tenantId): array;

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentSocialMediaByDate(string $date, int $tenantId): Collection;

    /**
     * Create new AdSpentSocialMedia data
     */
    public function createAdSpentSocialMedia(AdSpentSocialMediaRequest $request, int $tenantId): AdSpentSocialMedia;

    /**
     * Update AdSpentSocialMedia data
     */
    public function updateAdSpentSocialMedia(
        AdSpentSocialMedia $adSpentSocialMediaDAL,
        AdSpentSocialMediaRequest $request
    ): AdSpentSocialMedia;

    /**
     * Delete AdSpentSocialMedia data
     */
    public function deleteAdSpentSocialMedia(AdSpentSocialMedia $adSpentSocialMediaDAL): void;
}
