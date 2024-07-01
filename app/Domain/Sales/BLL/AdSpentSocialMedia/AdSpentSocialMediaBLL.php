<?php

namespace App\Domain\Sales\BLL\AdSpentSocialMedia;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Sales\DAL\AdSpentSocialMedia\AdSpentSocialMediaDAL;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdSpentSocialMediaBLL extends BaseBLL implements AdSpentSocialMediaBLLInterface
{
    public function __construct(
        protected AdSpentSocialMediaDAL $adSpentSocialMediaDAL,
        protected SalesBLLInterface $salesBLL,
        protected SocialMediaBLLInterface $socialMediaBLL
    ) {
    }

    /**
     * Return AdSpentSocialMedia data for DataTable
     */
    public function getAdSpentSocialMediaDataTable(Request $request, int $tenantId): Builder
    {
        $query = $this->adSpentSocialMediaDAL->getAdSpentSocialMediaDataTable();

        $query->where('tenant_id', $tenantId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        }

        if (! is_null($request->input('filterSocialMedia'))) {
            $query
                ->where('social_media_id', $request->input('filterSocialMedia'));
        }

        return $query;
    }

    /**
     * Retrieves AdSpent social media recap information based on the provided request.
     */
    public function getAdSpentSocialMediaRecap(Request $request, int $tenantId): array
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDateString = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDateString = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }

        // Get data by date
        $adSpent = $this->adSpentSocialMediaDAL
            ->getAdSpentSocialMediaByDateRange($startDateString, $endDateString, $tenantId);

        // Group by AdSpent social media
        $adSpentGrouped = $adSpent->groupBy('socialMedia.name');

        // Create recap AdSpent by social media
        $adSpentBySocialMedia = $adSpentGrouped->map(function ($item) {
            return $item->sum('amount');
        });

        $sumAdSpentByDate = $adSpent->groupBy('date')->map(function ($spent) {
            return $spent->sum('amount');
        });

        // Group AdSpent by AdSpent channel and then by date
        $sumAdSpentBySocialMediaAndDate = $adSpent->groupBy(function ($media) {
            return $media->socialMedia->name;
        })->map(function ($mediaSpent) {
            return $mediaSpent->groupBy('date')->map(function ($spent) {
                return $spent->sum('amount');
            });
        });

        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        $finalTotalBySocialMedia = [];
        foreach ($socialMedia as $media) {
            $finalTotalBySocialMedia[$media->name] = $adSpentBySocialMedia[$media->name] ?? 0;
        }

        $finalGroupedData = [];
        foreach ($socialMedia as $media) {
            $finalGroupedData[$media->name] = $sumAdSpentBySocialMediaAndDate[$media->name] ?? [];
        }

        return [
            'total' => $adSpentBySocialMedia->sum(),
            'bySocialMedia' => $finalTotalBySocialMedia,
            'adSpent' => $sumAdSpentByDate,
            'adSpentGrouped' => $finalGroupedData,
        ];
    }

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentSocialMediaByDate(string $date, int $tenantId): Collection
    {
        return $this->adSpentSocialMediaDAL->getAdSpentSocialMediaByDate($date, $tenantId);
    }

    /**
     * Create new AdSpentSocialMedia data
     */
    public function createAdSpentSocialMedia(AdSpentSocialMediaRequest $request, int $tenantId): AdSpentSocialMedia
    {
        try {
            DB::beginTransaction();

            $adSpentData = $this->adSpentSocialMediaDAL->createAdSpentSocialMedia($request, $tenantId);
            $this->salesBLL->createSales($adSpentData->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $adSpentData;
    }

    /**
     * Update AdSpentSocialMedia data
     */
    public function updateAdSpentSocialMedia(
        AdSpentSocialMedia $adSpentSocialMediaDAL,
        AdSpentSocialMediaRequest $request
    ): AdSpentSocialMedia {
        return $this->adSpentSocialMediaDAL->updateAdSpentSocialMedia($adSpentSocialMediaDAL, $request);
    }

    /**
     * Delete AdSpentSocialMedia data
     */
    public function deleteAdSpentSocialMedia(AdSpentSocialMedia $adSpentSocialMediaDAL): void
    {
        $this->adSpentSocialMediaDAL->deleteAdSpentSocialMedia($adSpentSocialMediaDAL);
    }
}
