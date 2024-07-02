<?php

namespace App\Domain\Order\BLL\Order;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Requests\OrderStoreRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface OrderBLLInterface extends BaseBLLInterface
{
    /**
     * Return order for DataTable
     */
    public function getOrderDataTable(Request $request, int $tenantId): Builder;

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
     * Import order
     */
    public function importOrder(Request $request, int $tenantId): void;
}
