<?php

namespace App\Domain\Marketing\BLL\Marketing;

use App\Domain\Marketing\DAL\Marketing\MarketingDALInterface;
use App\Domain\Marketing\Import\MarketingImport;
use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Requests\BrandingStoreRequest;
use App\Domain\Marketing\Requests\MarketingStoreRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Utilities\Request;

class MarketingBLL extends BaseBLL implements MarketingBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(
        protected MarketingDALInterface $marketingDAL
    ) {
    }

    /**
     * Return marketing for DataTable
     */
    public function getMarketingDataTable(Request $request, int $tenantId): Builder
    {

        $queryMarketing = $this->marketingDAL->getMarketingDataTable();

        $queryMarketing->where('tenant_id', $tenantId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $queryMarketing->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        }

        if (! is_null($request->input('filterMarketingType'))) {
            $queryMarketing->where('type', $request->input('filterMarketingType'));
        }

        if (! is_null($request->input('filterCategory'))) {
            $queryMarketing
                ->where('marketing_category_id', $request->input('filterCategory'));
        }

        if (! is_null($request->input('filterSubCategory'))) {
            $queryMarketing
                ->where('marketing_sub_category_id', $request->input('filterSubCategory'));
        }

        return $queryMarketing;
    }

    /**
     * Create marketing data type branding
     */
    public function createBranding(BrandingStoreRequest $request, int $tenantId): Marketing
    {
        try {
            DB::beginTransaction();

            $marketing = $this->marketingDAL->createBranding($request, $tenantId);
            $this->syncMarketingRecap($marketing->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $marketing;
    }

    /**
     * Update marketing data type branding
     */
    public function updateBranding(Marketing $marketing, BrandingStoreRequest $request, int $tenantId): Marketing
    {
        try {
            DB::beginTransaction();

            $marketing = $this->marketingDAL->updateBranding($marketing, $request);
            $this->syncMarketingRecap($marketing->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $marketing;
    }

    /**
     * Create marketing data type marketing
     */
    public function createMarketing(MarketingStoreRequest $request, int $tenantId): Marketing
    {
        try {
            DB::beginTransaction();

            $marketing = $this->marketingDAL->createMarketing($request, $tenantId);
            $this->syncMarketingRecap($marketing->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $marketing;
    }

    /**
     * Update marketing data type marketing
     */
    public function updateMarketing(Marketing $marketing, MarketingStoreRequest $request, int $tenantId): Marketing
    {
        try {
            DB::beginTransaction();

            $marketing = $this->marketingDAL->updateMarketing($marketing, $request);
            $this->syncMarketingRecap($marketing->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $marketing;
    }

    /**
     * Delete marketing
     */
    public function deleteMarketing(Marketing $marketing, int $tenantId): void
    {
        try {
            DB::beginTransaction();

            $this->marketingDAL->deleteMarketing($marketing);
            $this->syncMarketingRecap($marketing->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Import marketing
     *
     * @throws Exception
     */
    public function importMarketing(Request $request, int $tenantId): void
    {
        try {
            DB::beginTransaction();

            $import = new MarketingImport($tenantId);
            Excel::import($import, $request->file('fileMarketingImport'));

            $importedData = $import->getImportedData();
            if (! empty($importedData)) {
                $dates = array_column($importedData, 'date');
                $uniqueDates = array_unique($dates);

                foreach ($uniqueDates as $date) {
                    $this->syncMarketingRecap($date, $tenantId);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create marketing recap
     */
    public function syncMarketingRecap($date, int $tenantId): void
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');
        $marketingRecap = $this->marketingDAL->getMarketingDailySum($formattedDate, $tenantId);

        $marketingRecapToCreate = [
            'date' => $formattedDate,
            'total_marketing' => $marketingRecap->total_marketing,
            'total_branding' => $marketingRecap->total_branding,
        ];

        $this->marketingDAL->syncMarketingRecap($marketingRecapToCreate, $tenantId);
    }

    /**
     * Retrieves marketing recap information based on the provided request.
     */
    public function getMarketingRecap(Request $request, int $tenantId): array
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDateString = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDateString = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }

        $marketing = $this->marketingDAL->getMarketingByDateRange($startDateString, $endDateString, $tenantId);

        $pieChartData = $this->prepareChartData($marketing);

        return [
            'marketing' => $marketing,
            'marketing_expense' => $pieChartData['marketing'],
            'branding_expense' => $pieChartData['branding'],
            'pie_chart' => [
                'marketing' => $pieChartData['marketing'],
                'branding' => $pieChartData['branding'],
            ],
        ];
    }

    /**
     * Prepare data for pie chart
     */
    protected function prepareChartData(Collection $marketing): array
    {
        $sumMarketing = $marketing->sum('total_marketing');
        $sumBranding = $marketing->sum('total_branding');

        // Calculate percentages
        $totalSum = $sumMarketing + $sumBranding;

        if ($totalSum != 0) {
            $marketingPercentage = round(($sumMarketing / $totalSum) * 100);
            $brandingPercentage = round(($sumBranding / $totalSum) * 100);
        } else {
            // Set percentages to 0 if the total sum is 0 to avoid division by zero
            $marketingPercentage = 0;
            $brandingPercentage = 0;
        }

        return [
            'sumMarketing' => $this->numberFormat($sumMarketing),
            'sumBranding' => $this->numberFormat($sumBranding),
            'marketing' => $marketingPercentage,
            'branding' => $brandingPercentage,
        ];
    }

    /**
     * Add separator on number and round the value
     */
    protected function numberFormat(int $number): string
    {
        return number_format(round($number), 0, ',', '.');
    }
}
