<?php

namespace App\Domain\Sales\DAL\Sales;

use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\SalesByChannel;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SalesDAL extends BaseDAL implements SalesDALInterface
{
    public function __construct(
        protected Sales $sales,
        protected SalesByChannel $salesByChannel
    ) {
    }

    /**
     * Return sales for DataTable
     */
    public function getSalesDataTable(): Builder
    {
        return $this->sales->query()
            ->leftJoin('funnel_totals', function ($join) {
                $join->on('sales.date', '=', 'funnel_totals.date')
                    ->on('sales.tenant_id', '=', 'funnel_totals.tenant_id');
            })
            ->select(
                'sales.*',
                'funnel_totals.total_spend as total_spend'
            );
    }

    /**
     * Create sales
     */
    public function createSales(array $salesToCreate): Sales
    {
        return $this->sales->updateOrCreate([
            'date' => Carbon::parse($salesToCreate['date'])->format('Y-m-d'),
            'tenant_id' => $salesToCreate['tenant_id'],
        ], [
            ...$salesToCreate,
        ]);
    }

    /**
     * Create sales by channel
     */
    public function createSalesByChannel(array $salesToCreate): SalesByChannel
    {
        return $this->salesByChannel->updateOrCreate([
            'date' => Carbon::parse($salesToCreate['date'])->format('Y-m-d'),
            'sales_channel_id' => $salesToCreate['sales_channel_id'],
            'tenant_id' => $salesToCreate['tenant_id'],
        ], [
            ...$salesToCreate,
        ]);
    }

    /**
     * Find sales data by date
     */
    public function findSalesByDate($date, int $tenantId): ?Sales
    {
        return $this->sales
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date)->format('Y-m-d'))
            ->first();
    }

    /**
     * Get sales by date range
     */
    public function getSalesByDateRange($startDate, $endDate, int $tenantId): Collection
    {
        return $this->sales->where('date', '>=', Carbon::parse($startDate))
            ->where('tenant_id', $tenantId)
            ->where('date', '<=', Carbon::parse($endDate))
            ->orderBy('date', 'ASC')
            ->get();
    }

    /**
     * Get sales by channel by date range
     */
    public function getSalesByChannelByDateRange($startDate, $endDate, int $tenantId, $channelId = null): Collection
    {
        $query = $this->salesByChannel->query()
            ->with('salesChannel')
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', Carbon::parse($startDate))
            ->where('date', '<=', Carbon::parse($endDate))
            ->orderBy('date', 'ASC');

        $query->when(! is_null($channelId), function ($q) use ($channelId) {
            $q->where('sales_channel_id', $channelId);
        });

        return $query->get();
    }
}
