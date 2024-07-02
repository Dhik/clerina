<?php

namespace App\Domain\Funnel\BLL\Funnel;

use App\Domain\Funnel\DAL\Funnel\FunnelDALInterface;
use App\Domain\Funnel\Enums\FunnelTypeEnum;
use App\Domain\Funnel\Models\Funnel;
use App\Domain\Funnel\Models\FunnelTotal;
use App\Domain\Funnel\Requests\CreateFunnelBofuRequest;
use App\Domain\Funnel\Requests\CreateFunnelMofuRequest;
use App\Domain\Funnel\Requests\CreateFunnelTofuRequest;
use App\Domain\Funnel\Requests\StoreScreenShotRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Utilities\Request;

/**
 * @property FunnelDALInterface DAL
 */
class FunnelBLL extends BaseBLL implements FunnelBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(protected FunnelDALInterface $funnelDAL)
    {
    }

    /**
     * Return funnel for DataTable
     */
    public function getFunnelDataTable(Request $request): Builder
    {
        $queryFunnel = $this->funnelDAL->getFunnelDataTable();

        $queryFunnel->where('type', $request->input('type'));
        $queryFunnel->where('social_media_id', $request->input('social_media_id'));

        if (! is_null($request->input('filterDates'))) {
            $date = Carbon::createFromFormat('m/Y', $request->input('filterDates'));

            $startDateString = $date->copy()->startOfMonth()->format('Y-m-d');
            $endDateString = $date->copy()->endOfMonth()->format('Y-m-d');

            $queryFunnel
                ->where('date', '>=', $startDateString)
                ->where('date', '<=', $endDateString);
        }

        return $queryFunnel->orderBy('date', 'ASC');
    }

    /**
     * Return funnel recap for DataTable
     */
    public function getFunnelRecapDataTable(Request $request): Builder
    {
        $queryFunnel = $this->funnelDAL->getFunnelRecapDataTable();

        $queryFunnel->where('type', $request->input('type'));

        if (! is_null($request->input('filterDates'))) {
            $date = Carbon::createFromFormat('m/Y', $request->input('filterDates'));

            $startDateString = $date->copy()->startOfMonth()->format('Y-m-d');
            $endDateString = $date->copy()->endOfMonth()->format('Y-m-d');

            $queryFunnel
                ->where('date', '>=', $startDateString)
                ->where('date', '<=', $endDateString);
        }

        return $queryFunnel->orderBy('date', 'ASC');
    }

    /**
     * Return funnel total for DataTable
     */
    public function getFunnelTotalDataTable(Request $request): Builder
    {
        $queryFunnel = $this->funnelDAL->getFunnelTotalDataTable();

        if (! is_null($request->input('filterDates'))) {
            $date = Carbon::createFromFormat('m/Y', $request->input('filterDates'));

            $startDateString = $date->copy()->startOfMonth()->format('Y-m-d');
            $endDateString = $date->copy()->endOfMonth()->format('Y-m-d');

            $queryFunnel
                ->where('date', '>=', $startDateString)
                ->where('date', '<=', $endDateString);
        }

        return $queryFunnel->orderBy('date', 'ASC');
    }

    /**
     * Create TOFU
     *
     * @throws Exception
     */
    public function createTOFU(CreateFunnelTofuRequest $request): void
    {
        $spend = $request->input('spend');
        $reach = $request->input('reach');
        $impression = $request->input('impression');
        $linkClick = $request->input('link_click');

        $tofuToCreate = [
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date')),
            'social_media_id' => $request->input('social_media_id'),
            'type' => FunnelTypeEnum::TOFU,
            'spend' => $spend,
            'reach' => $reach,
            'cpr' => $this->calculateCPR($spend, $reach),
            'impression' => $impression,
            'cpm' => $this->calculateCPM($spend, $impression),
            'frequency' => $this->calculateFrequency($impression, $reach),
            'cpv' => $request->input('cpv'),
            'play_video' => $request->input('play_video'),
            'link_click' => $linkClick,
            'cpc' => $this->calculateCPC($spend, $linkClick),
        ];

        try {
            DB::beginTransaction();

            $createdTofu = $this->funnelDAL->createFunnel($tofuToCreate);
            $this->syncRecapTOFU($createdTofu->date);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create MOFU
     *
     * @throws Exception
     */
    public function createMOFU(CreateFunnelMofuRequest $request): void
    {
        $spend = $request->input('spend');
        $reach = $request->input('reach');
        $impression = $request->input('impression');
        $linkClick = $request->input('link_click');
        $engagement = $request->input('engagement');

        $mofuToCreate = [
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date')),
            'social_media_id' => $request->input('social_media_id'),
            'type' => FunnelTypeEnum::MOFU,
            'spend' => $spend,
            'reach' => $reach,
            'impression' => $impression,
            'engagement' => $engagement,
            'cpe' => $this->calculateCPE($spend, $engagement),
            'cpm' => $this->calculateCPM($spend, $impression),
            'frequency' => $this->calculateFrequency($impression, $reach),
            'cpc' => $this->calculateCPC($spend, $linkClick),
            'link_click' => $linkClick,
            'ctr' => $request->input('ctr'),
            'cplv' => $request->input('cplv'),
            'cpa' => $request->input('cpa'),
        ];

        try {
            DB::beginTransaction();

            $createdMofu = $this->funnelDAL->createFunnel($mofuToCreate);
            $this->syncRecapMOFU($createdMofu->date);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create BOFU
     *
     * @throws Exception
     */
    public function createBOFU(CreateFunnelBofuRequest $request): void
    {
        $spend = $request->input('spend');
        $atc = $request->input('atc');
        $initiatedCheckoutNumber = $request->input('initiated_checkout_number');
        $purchaseNumber = $request->input('purchase_number');

        $bofuTOCreate = [
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date')),
            'social_media_id' => $request->input('social_media_id'),
            'type' => FunnelTypeEnum::BOFU,
            'spend' => $spend,
            'atc' => $atc,
            'initiated_checkout_number' => $initiatedCheckoutNumber,
            'purchase_number' => $purchaseNumber,
            'cost_per_ic' => $this->calculateCostPerIC($spend, $initiatedCheckoutNumber),
            'cost_per_atc' => $this->calculateCostPerATC($spend, $atc),
            'cost_per_purchase' => $this->calculateCostPerPurchase($spend, $purchaseNumber),
            'roas' => $request->input('roas'),
            'frequency' => $request->input('frequency'),
        ];

        try {
            DB::beginTransaction();

            $createdBofu = $this->funnelDAL->createFunnel($bofuTOCreate);
            $this->syncRecapBOFU($createdBofu->date);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Sync recap tofu
     *
     * @throws Exception
     */
    public function syncRecapTOFU(string $date): void
    {
        $funnels = $this->funnelDAL->getFunnelByDate($date, FunnelTypeEnum::TOFU);
        $countFunnels = $funnels->count();

        $tofuRecapToCreate = [
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'type' => FunnelTypeEnum::TOFU,
            'spend' => $funnels->sum('spend'),
            'reach' => $funnels->sum('reach'),
            'cpr' => $this->calculateRecapCPR($funnels->sum('cpr'), $countFunnels),
            'impression' => $funnels->sum('impression'),
            'cpm' => $this->calculateRecapCPM($funnels->sum('cpm'), $countFunnels),
            'frequency' => $this->calculateRecapFrequency($funnels->sum('frequency'), $countFunnels),
            'cpv' => $this->calculateRecapCPV($funnels->sum('cpv'), $countFunnels),
            'play_video' => $funnels->sum('play_video'),
            'link_click' => $funnels->sum('link_click'),
            'cpc' => $this->calculateRecapCPC($funnels->sum('cpc'), $countFunnels),
        ];

        try {
            DB::beginTransaction();

            $this->funnelDAL->createFunnelRecap($tofuRecapToCreate);
            $this->syncTotalFunnel($date);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Sync recap mofu
     *
     * @throws Exception
     */
    public function syncRecapMofu(string $date): void
    {
        $funnels = $this->funnelDAL->getFunnelByDate($date, FunnelTypeEnum::MOFU);
        $countFunnels = $funnels->count();

        $mofuRecapToCreate = [
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'type' => FunnelTypeEnum::MOFU,
            'spend' => $funnels->sum('spend'),
            'reach' => $funnels->sum('reach'),
            'impression' => $funnels->sum('impression'),
            'engagement' => $funnels->sum('engagement'),
            'cpe' => $this->calculateRecapCPE($funnels->sum('cpe'), $countFunnels),
            'cpm' => $this->calculateRecapCPM($funnels->sum('cpm'), $countFunnels),
            'frequency' => $this->calculateRecapFrequency($funnels->sum('frequency'), $countFunnels),
            'cpc' => $this->calculateRecapCPC($funnels->sum('cpc'), $countFunnels),
            'link_click' => $funnels->sum('link_click'),
            'ctr' => $this->calculateRecapCTR($funnels->sum('ctr'), $countFunnels),
            'cplv' => $this->calculateRecapCPLV($funnels->sum('cplv'), $countFunnels),
            'cpa' => $this->calculateRecapCPA($funnels->sum('cpa'), $countFunnels),
        ];

        try {
            DB::beginTransaction();

            $this->funnelDAL->createFunnelRecap($mofuRecapToCreate);
            $this->syncTotalFunnel($date);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Sync recap bofu
     *
     * @throws Exception
     */
    public function syncRecapBofu(string $date): void
    {
        $funnels = $this->funnelDAL->getFunnelByDate($date, FunnelTypeEnum::BOFU);
        $countFunnels = $funnels->count();

        $bofuRecapToCreate = [
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'type' => FunnelTypeEnum::BOFU,
            'spend' => $funnels->sum('spend'),
            'atc' => $funnels->sum('atc'),
            'initiated_checkout_number' => $funnels->sum('initiated_checkout_number'),
            'purchase_number' => $funnels->sum('purchase_number'),
            'cost_per_ic' => $this->calculateRecapCostPerIC($funnels->sum('cost_per_ic'), $countFunnels),
            'cost_per_atc' => $this->calculateRecapCostPerATC($funnels->sum('cost_per_atc'), $countFunnels),
            'cost_per_purchase' => $this->calculateRecapCostPerPurchase($funnels->sum('cost_per_purchase'), $countFunnels),
            'roas' => $this->calculateRecapRoas($funnels->sum('roas'), $countFunnels),
            'frequency' => $this->calculateRecapFrequency($funnels->sum('frequency'), $countFunnels),
        ];

        try {
            DB::beginTransaction();

            $this->funnelDAL->createFunnelRecap($bofuRecapToCreate);
            $this->syncTotalFunnel($date);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Sync total recap
     */
    public function syncTotalFunnel(string $date): void
    {
        $funnels = $this->funnelDAL->getFunnelRecapByDate($date);

        $totalToCreate = [
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'total_reach' => $funnels->sum('reach'),
            'total_impression' => $funnels->sum('impression'),
            'total_engagement' => $funnels->sum('engagement'),
            'total_cpm' => $this->calculateRecapCPM($funnels->sum('cpm'), 2),
            'total_roas' => $funnels->sum('roas'),
            'total_spend' => $funnels->sum('spend'),
        ];

        $this->funnelDAL->createFunnelTotal($totalToCreate);
    }

    /**
     * Store screenshot
     */
    public function storeScreenshot(FunnelTotal $funnelTotal, StoreScreenShotRequest $request): void
    {
        $funnelTotal->clearMediaCollection('screenshot');
        $funnelTotal->addMedia($request->file('image'))
            ->toMediaCollection('screenshot', 'private');
    }

    /**
     * Formula for CPR,
     * Spend/Reach
     */
    protected function calculateCPR(?int $spend, ?int $reach): float|int
    {
        if ($reach > 0) {
            $result = ceil(($spend / $reach) * 1000);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for CPM
     * Spend/Impression
     */
    protected function calculateCPM(?int $spend, ?int $impression): float|int
    {
        if ($impression > 0) {
            $result = round(($spend / $impression) * 1000);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for Frequency
     * Impression/Reach
     */
    protected function calculateFrequency(?int $impression, ?int $reach): float|int
    {
        if ($reach > 0) {
            $result = round($impression / $reach, 2);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for CPC
     * Spend/Link Click
     */
    protected function calculateCPC(?int $spend, ?int $linkClick): float|int
    {
        if ($linkClick > 0) {
            $result = $this->ceilDecimal($spend / $linkClick);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for CPE
     * Spend/Engagement
     */
    protected function calculateCPE(?int $spend, ?int $engagement): float|int
    {
        if ($engagement > 0) {
            $result = ceil($spend / $engagement);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for Cost per IC
     * Spend/Engagement
     */
    protected function calculateCostPerIC(?int $spend, ?int $initiatedCheckoutNumber): float|int
    {
        if ($initiatedCheckoutNumber > 0) {
            $result = ceil($spend / $initiatedCheckoutNumber);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for Cost per ATC
     * Spend/ATC
     */
    protected function calculateCostPerATC(?int $spend, ?int $atc): float|int
    {
        if ($atc > 0) {
            $result = ceil($spend / $atc);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for Cost per Purchase
     * Spend/Purchase Number
     */
    protected function calculateCostPerPurchase(?int $spend, ?int $purchaseNumber): float|int
    {
        if ($purchaseNumber > 0) {
            $result = ceil($spend / $purchaseNumber);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPR
     * Total CPR/Count CPR
     */
    protected function calculateRecapCPR(?int $totalCPR, ?int $countCPR): float|int
    {
        if ($countCPR > 0) {
            $result = ceil($totalCPR / $countCPR);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPM
     * Total CPM/Count CPM
     */
    protected function calculateRecapCPM(?int $totalCPM, ?int $countCPM): float|int
    {
        if ($countCPM > 0) {
            $result = ceil($totalCPM / $countCPM);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average Frequency
     * Total Frequency/Count Frequency
     */
    protected function calculateRecapFrequency(int|float|null $totalFrequency, ?int $countFrequency): float|int
    {
        if ($countFrequency > 0) {
            $result = round($totalFrequency / $countFrequency, 2);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPV
     * Total CPM/Count CPM
     */
    protected function calculateRecapCPV(?int $totalCPV, ?int $countCPV): float|int
    {
        if ($countCPV > 0) {
            $result = ceil($totalCPV / $countCPV);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPC
     * Total CPC/Count CPC
     */
    protected function calculateRecapCPC(?int $totalCPC, ?int $countCPC): float|int
    {
        if ($countCPC > 0) {
            $result = ceil($totalCPC / $countCPC);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPE
     * Total CPE/Count CPE
     */
    protected function calculateRecapCPE(?int $totalCPE, ?int $countCPE): float|int
    {
        if ($totalCPE > 0) {
            $result = ceil($totalCPE / $countCPE);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CTR
     * Total CTR/Count CTR
     */
    protected function calculateRecapCTR(int|float|null $totalCTR, ?int $countCTR): float|int
    {
        if ($totalCTR > 0) {
            $result = round($totalCTR / $countCTR, 2);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPLV
     * Total CPLV/Count CPLV
     */
    protected function calculateRecapCPLV(?int $totalCPLV, ?int $countCPLV): float|int
    {
        if ($totalCPLV > 0) {
            $result = ceil($totalCPLV / $countCPLV);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average CPA
     * Total CPA/Count CPA
     */
    protected function calculateRecapCPA(?int $totalCPA, ?int $countCPA): float|int
    {
        if ($totalCPA > 0) {
            $result = ceil($totalCPA / $countCPA);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average cost per IC
     * Total Cost per IC/Cost per IC
     */
    protected function calculateRecapCostPerIC(?int $totalCostPerIC, ?int $countCostPerIC): float|int
    {
        if ($totalCostPerIC > 0) {
            $result = ceil($totalCostPerIC / $countCostPerIC);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average cost per ATC
     * Total Cost per ATC
     */
    protected function calculateRecapCostPerATC(?int $totalCostPerATC, ?int $countCostPerATC): float|int
    {
        if ($totalCostPerATC > 0) {
            $result = ceil($totalCostPerATC / $countCostPerATC);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average cost per Purchase
     * Total Cost per Purchase
     */
    protected function calculateRecapCostPerPurchase(?int $totalCostPerPurchase, ?int $countCostPerPurchase): float|int
    {
        if ($totalCostPerPurchase > 0) {
            $result = ceil($totalCostPerPurchase / $countCostPerPurchase);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Formula for find average ROAS
     * Total Cost per ROAS
     */
    protected function calculateRecapRoas(int|float|null $totalRoas, ?int $countRoas): float|int
    {
        if ($totalRoas > 0) {
            $result = round($totalRoas / $countRoas, 2);
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Ceil 2 decimal
     */
    protected function ceilDecimal(?int $number): float|int|null
    {
        $decimalPlaces = 2;

        if ($number > 0) {
            $number = ceil($number * pow(10, $decimalPlaces)) / pow(10, $decimalPlaces);
        }

        return $number;
    }
}
