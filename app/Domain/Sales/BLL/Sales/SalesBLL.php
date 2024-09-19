<?php

namespace App\Domain\Sales\BLL\Sales;

use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\DAL\AdSpentMarketPlace\AdSpentMarketPlaceDALInterface;
use App\Domain\Sales\DAL\AdSpentSocialMedia\AdSpentSocialMediaDALInterface;
use App\Domain\Sales\DAL\Sales\SalesDALInterface;
use App\Domain\Sales\DAL\Visit\VisitDALInterface;
use App\Domain\Campaign\DAL\Campaign\CampaignDALInterface;
use App\Domain\Sales\Models\Sales;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Utilities\Request;

/**
 * @property SalesDALInterface DAL
 */
class SalesBLL extends BaseBLL implements SalesBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(
        protected AdSpentMarketPlaceDALInterface $adSpentMarketPlaceDAL,
        protected AdSpentSocialMediaDALInterface $adSpentSocialMediaDAL,
        protected OrderDALInterface $orderDAL,
        protected SalesDALInterface $salesDAL,
        protected SalesChannelBLLInterface $salesChannelBLL,
        protected VisitDALInterface $visitDAL,
        protected CampaignDALInterface $campaignDAL,
    ) {
    }

    /**
     * Return sales for DataTable
     */
    public function getSalesDataTable(Request $request, int $tenantId): Builder
    {
        $querySales = $this->salesDAL->getSalesDataTable();

        $querySales->where('sales.tenant_id', $tenantId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $querySales->where('sales.date', '>=', $startDate)
                ->where('sales.date', '<=', $endDate);
        }

        return $querySales;
    }

    /**
     * Retrieves sales recap information based on the provided request.
     */
    public function getSalesRecap(Request $request, int $tenantId): array
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDateString = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDateString = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }

        $sales = $this->salesDAL->getSalesByDateRange($startDateString, $endDateString, $tenantId);
        $channelId = $request->input('filterChannel');

        $salesByChannelByDate = $this->salesDAL
            ->getSalesByChannelByDateRange($startDateString, $endDateString, $tenantId);

        if (! is_null($channelId)) {
            $salesByChannel = $salesByChannelByDate->where('sales_channel_id', $channelId);
        }

        $tempSales = $channelId ? $salesByChannel->sum('turnover') : $sales->sum('turnover');
        $tempVisit = $channelId ? $salesByChannel->sum('visit') : $sales->sum('visit');
        $tempOrder = $channelId ? $salesByChannel->sum('order') : $sales->sum('order');
        $tempQty = $channelId ? $salesByChannel->sum('qty') : $sales->sum('qty');
        $tempClosingRate = $channelId ?
            ($salesByChannel->count() === 0 ? 0 : $salesByChannel->sum('closing_rate') / $salesByChannel->count()) :
            ($sales->count() === 0 ? 0 : $sales->sum('closing_rate') / $sales->count());

        $campaigns = $this->campaignDAL->getCampaignsByDateRange($startDateString, $endDateString, $tenantId);
        $totalCampaignExpense = $campaigns->sum('total_expense');
        
        $totalAdSpent = $sales->sum('ad_spent_total') + $totalCampaignExpense;

        return [
            'sales' => $sales,
            'total_sales' => $this->numberFormat($tempSales),
            'total_visit' => $this->numberFormat($tempVisit),
            'total_order' => $this->numberFormat($tempOrder),
            'total_qty' => $this->numberFormat($tempQty),
            'campaign_expense' => $this->numberFormat($totalCampaignExpense),
            'total_ad_spent' => $this->numberFormat($totalAdSpent),
            'total_ads_spent' => $this->numberFormat($sales->sum('ad_spent_total')),
            'total_roas' => $totalAdSpent === 0 ? 0 : $this->numberFormat($tempSales / $totalAdSpent, 2),
            'cpa' => $tempOrder === 0 ? 0 : $this->numberFormat($sales->sum('ad_spent_total') / $tempOrder, 0),
            'closing_rate' => $tempVisit === 0 ? 0 : $this->numberFormat(($tempOrder / $tempVisit) * 100, 2) . '%',
            'pie_chart' => $this->preparePieChartData($salesByChannelByDate),
        ];
    }

    /**
     * Prepare data for Pie Chart
     */
    protected function preparePieChartData(Collection $salesByChannelByDate): array
    {
        $pieChartData = $salesByChannelByDate->groupBy('salesChannel.name')
            ->map(function ($items, $channelName) {
                return $items->sum('order');
            });

        $pieChartDataArray = $pieChartData->toArray();

        // Calculate the total sum of values
        $total = array_sum($pieChartDataArray);

        // Calculate the percentage for each channel
        $percentageData = [];
        foreach ($pieChartData as $channel => $value) {
            if ($total != 0) {
                $percentage = round(($value / $total) * 100);
            } else {
                $percentage = 0;
            }

            $percentageData[$channel] = $percentage;
        }

        return $percentageData;
    }

    /**
     * Add separator on number and round the value
     */
    protected function numberFormat(int $number, $decimals = 0): string
    {
        return number_format(round($number), 0, ',', '.');
    }

    /**
     * Create sales
     *
     * @throws Exception
     */
    public function createSales($date, int $tenantId): Sales
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        // Sum data AdSpent
        $sumSpentSocialMedia = $this->adSpentSocialMediaDAL->sumTotalAdSpentPerDay($formattedDate, $tenantId);
        $sumSpentMarketPlace = $this->adSpentMarketPlaceDAL->sumTotalAdSpentPerDay($formattedDate, $tenantId);
        $totalAdSpent = $sumSpentSocialMedia + $sumSpentMarketPlace;

        // Get raw data Visit and sum it
        $visitData = $this->visitDAL->getVisitByDate($formattedDate, $tenantId);

        $totalVisit = $visitData->sum('visit_amount');

        // Get raw data order
        $recapOrder = $this->orderDAL->getOrderDailySum($formattedDate, $tenantId);

        $salesToCreate = [
            'date' => $formattedDate,
            'tenant_id' => $tenantId,
            'visit' => $totalVisit,
            'ad_spent_social_media' => $sumSpentSocialMedia,
            'ad_spent_market_place' => $sumSpentMarketPlace,
            'ad_spent_total' => $totalAdSpent,
            'qty' => $recapOrder->qty,
            'order' => $recapOrder->total_order,
            'turnover' => $recapOrder->amount,
            'closing_rate' => $this->calculateClosingRate($recapOrder->total_order, $totalVisit),
            'roas' => $this->calculateROAS($recapOrder->amount, $totalAdSpent ?? 0),
        ];

        try {
            DB::beginTransaction();

            $this->createSalesByChannel($formattedDate, $recapOrder->recapBySalesChannel, $visitData, $tenantId);
            $sales = $this->salesDAL->createSales($salesToCreate);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $sales;
    }

    protected function createSalesByChannel($date, $recapOrder, $visits, $tenantId): void
    {
        // Get all sales channel
        $salesChannel = $this->salesChannelBLL->getSalesChannel();

        // Group visit amount by channel
        $visitByChannel = $visits->groupBy('sales_channel_id')
            ->map(function ($items, $channelId) {
                $visitAmount = $items->sum('visit_amount');

                return [
                    'channel_id' => $channelId,
                    'visit_amount' => $visitAmount,
                ];
            });

        foreach ($salesChannel as $channel) {

            $tempOrder = $recapOrder[$channel->id]['total_order'] ?? 0;
            $tempVisit = $visitByChannel[$channel->id]['visit_amount'] ?? 0;

            $salesToCreate = [
                'date' => $date,
                'sales_channel_id' => $channel->id,
                'tenant_id' => $tenantId,
                'qty' => $recapOrder[$channel->id]['qty'] ?? 0,
                'order' => $tempOrder,
                'turnover' => $recapOrder[$channel->id]['amount'] ?? 0,
                'visit' => $tempVisit,
                'closing_rate' => $this->calculateClosingRate($tempOrder, $tempVisit),
            ];

            $this->salesDAL->createSalesByChannel($salesToCreate);
        }
    }

    /**
     * Calculate ROAS
     * Total sales / Total AdSpent
     */
    protected function calculateROAS(int $salesAmount, int $totalAdSpent): float|int
    {
        return $totalAdSpent === 0 ? 0 : round($salesAmount / $totalAdSpent, 2);
    }

    /**
     * Calculate closing rate
     * Total order / Total visit
     */
    protected function calculateClosingRate(int $totalOrder, int $totalVisit): float|int
    {
        return $totalOrder === 0 ? 0 : round(($totalVisit / $totalOrder) * 100);
    }
}
