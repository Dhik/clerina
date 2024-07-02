<?php

namespace App\Domain\Sales\DAL\AdSpentSocialMedia;

use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AdSpentSocialMediaDAL extends BaseDAL implements AdSpentSocialMediaDALInterface
{
    public function __construct(protected AdSpentSocialMedia $adSpentSocialMedia)
    {
    }

    /**
     * Return AdSpentSocialMedia data for DataTable
     */
    public function getAdSpentSocialMediaDataTable(): Builder
    {
        return $this->adSpentSocialMedia->query()->with('socialMedia');
    }

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentSocialMediaByDate(string $date, int $tenantId): Collection
    {
        return $this->adSpentSocialMedia->with('socialMedia')
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get AdSpentSocialMedia by date range
     */
    public function getAdSpentSocialMediaByDateRange($startDate, $endDate, int $tenantId): Collection
    {
        return $this->adSpentSocialMedia->with('socialMedia')
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', Carbon::parse($startDate))
            ->where('date', '<=', Carbon::parse($endDate))
            ->orderBy('date', 'ASC')
            ->get();
    }

    /**
     * Create new AdSpentSocialMedia data
     */
    public function createAdSpentSocialMedia(AdSpentSocialMediaRequest $request, int $tenantId): AdSpentSocialMedia
    {
        return $this->adSpentSocialMedia->updateOrCreate([
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d'),
            'social_media_id' => $request->input('social_media_id'),
            'tenant_id' => $tenantId,
        ], [
            'amount' => $request->input('amount'),
        ]);
    }

    /**
     * Update AdSpentSocialMedia data
     */
    public function updateAdSpentSocialMedia(
        AdSpentSocialMedia $adSpentSocialMedia,
        AdSpentSocialMediaRequest $request
    ): AdSpentSocialMedia {
        $adSpentSocialMedia->date = Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $adSpentSocialMedia->social_media_id = $request->input('social_media_id');
        $adSpentSocialMedia->amount = $request->input('amount');
        $adSpentSocialMedia->update();

        return $adSpentSocialMedia;
    }

    /**
     * Delete AdSpentSocialMedia data
     */
    public function deleteAdSpentSocialMedia(AdSpentSocialMedia $adSpentSocialMedia): void
    {
        $adSpentSocialMedia->delete();
    }

    /**
     * Sum total adSpent by date
     */
    public function sumTotalAdSpentPerDay($date, int $tenantId): mixed
    {
        return $this->adSpentSocialMedia
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->sum('amount');
    }

    /**
     * Check if adSpent have social media
     */
    public function checkAdSpentBySocialMedia(int $socialMediaId, int $tenantId): ?AdSpentSocialMedia
    {
        return $this->adSpentSocialMedia
            ->where('tenant_id', $tenantId)
            ->where('social_media_id', $socialMediaId)
            ->first();
    }
}
