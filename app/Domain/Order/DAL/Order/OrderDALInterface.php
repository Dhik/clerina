<?php

namespace App\Domain\Order\DAL\Order;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Requests\OrderStoreRequest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;

interface OrderDALInterface extends BaseDALInterface
{
    /**
     * Return order for DataTable
     */
    public function getOrderDataTable(): Builder;

    /**
     * Create a new order
     */
    public function createOrder(OrderStoreRequest $request, int $tenantId): Order;

    /**
     * Update an order
     */
    public function updateOrder(Order $order, OrderStoreRequest $request): Order;

    /**
     * Delete an order
     */
    public function deleteOrder(Order $order): void;

    /**
     * Get order by date
     */
    public function getOrderByDate($date, int $tenantId): mixed;

    /**
     * Return recap order by date
     */
    public function getOrderDailySum($date, int $tenantId): object;

    /**
     * Check if order have sales channel
     */
    public function checkOrderBySalesChannel(int $salesChannelId, int $tenantId): ?Order;
}
