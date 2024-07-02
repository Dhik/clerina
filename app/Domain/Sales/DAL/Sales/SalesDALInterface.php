<?php

namespace App\Domain\Sales\DAL\Sales;

use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\SalesByChannel;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface SalesDALInterface extends BaseDALInterface
{
    /**
     * Return sales for DataTable
     */
    public function getSalesDataTable(): Builder;

    /**
     * Create sales
     */
    public function createSales(array $salesToCreate): Sales;

    /**
     * Create sales by channel
     */
    public function createSalesByChannel(array $salesToCreate): SalesByChannel;

    /**
     * Find sales data by date
     */
    public function findSalesByDate($date, int $tenantId): ?Sales;

    /**
     * Get sales by date range
     */
    public function getSalesByDateRange($startDate, $endDate, int $tenantId): Collection;

    /**
     * Get sales by channel by date range
     */
    public function getSalesByChannelByDateRange($startDate, $endDate, int $tenantId, $channelId = null): Collection;
}
