<?php

namespace App\Domain\Sales\DAL\AdSpentSocialMedia;

use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface AdSpentSocialMediaDALInterface extends BaseDALInterface
{
    /**
     * Return AdSpentSocialMedia data for DataTable
     */
    public function getAdSpentSocialMediaDataTable(): Builder;

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentSocialMediaByDate(string $date, int $tenantId): Collection;

    /**
     * Get AdSpentSocialMedia by date range
     */
    public function getAdSpentSocialMediaByDateRange($startDate, $endDate, int $tenantId): Collection;

    /**
     * Create new AdSpentSocialMedia data
     */
    public function createAdSpentSocialMedia(AdSpentSocialMediaRequest $request, int $tenantId): AdSpentSocialMedia;

    /**
     * Update AdSpentSocialMedia data
     */
    public function updateAdSpentSocialMedia(
        AdSpentSocialMedia $adSpentSocialMedia,
        AdSpentSocialMediaRequest $request
    ): AdSpentSocialMedia;

    /**
     * Delete AdSpentSocialMedia data
     */
    public function deleteAdSpentSocialMedia(AdSpentSocialMedia $adSpentSocialMedia): void;

    /**
     * Sum total adSpent by date
     */
    public function sumTotalAdSpentPerDay($date, int $tenantId): mixed;

    /**
     * Check if adSpent have social media
     */
    public function checkAdSpentBySocialMedia(int $socialMediaId, int $tenantId): ?AdSpentSocialMedia;
}
