<?php

namespace App\Domain\Order\DAL\Order;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Requests\OrderStoreRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class OrderDAL extends BaseDAL implements OrderDALInterface
{
    public function __construct(protected Order $order)
    {
    }

    /**
     * Return order for DataTable
     */
    public function getOrderDataTable(): Builder
    {
        return $this->order->query()
            ->with('salesChannel');
    }

    /**
     * Create a new order
     */
    public function createOrder(OrderStoreRequest $request, int $tenantId): Order
    {
        return Order::create([
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date')),
            'id_order' => $request->input('id_order'),
            'sales_channel_id' => $request->input('sales_channel_id'),
            'customer_name' => $request->input('customer_name'),
            'customer_phone_number' => $request->input('customer_phone_number'),
            'product' => $request->input('product'),
            'qty' => $request->input('qty'),
            'receipt_number' => $request->input('receipt_number'),
            'shipment' => $request->input('shipment'),
            'payment_method' => $request->input('payment_method'),
            'sku' => $request->input('sku'),
            'variant' => $request->input('variant'),
            'price' => $request->input('price'),
            'username' => $request->input('username'),
            'shipping_address' => $request->input('shipping_address'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'amount' => $request->input('qty') * $request->input('price'),
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * Update an order
     */
    public function updateOrder(Order $order, OrderStoreRequest $request): Order
    {
//        dd($request->input('qty'));
        $order->date = Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $order->id_order = $request->input('id_order');
        $order->sales_channel_id = $request->input('sales_channel_id');
        $order->customer_name = $request->input('customer_name');
        $order->customer_phone_number = $request->input('customer_phone_number');
        $order->product = $request->input('product');
        $order->receipt_number = $request->input('receipt_number');
        $order->shipment = $request->input('shipment');
        $order->sku = $request->input('sku');
        $order->variant = $request->input('variant');
        $order->price = $request->input('price');
        $order->username = $request->input('username');
        $order->shipping_address = $request->input('shipping_address');
        $order->province = $request->input('province');
        $order->city = $request->input('city');
        $order->qty = $request->input('qty');
        $order->province = $request->input('province');
        $order->amount = $request->input('qty') * $request->input('price');
        $order->update();

        return $order;
    }

    /**
     * Delete an order
     */
    public function deleteOrder(Order $order): void
    {
        $order->delete();
    }

    /**
     * Get order by date
     */
    public function getOrderByDate($date, int $tenantId): mixed
    {
        return $this->order
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->get();
    }

    /**
     * Return recap order by date
     */
    public function getOrderDailySum($date, int $tenantId): object
    {
        $orderByDate = $this->getOrderByDate($date, $tenantId);

        $recapBySalesChannel = $orderByDate->groupBy('sales_channel_id')
            ->map(function ($items, $channelName) {
                $totalQty = $items->sum('qty');
                $totalAmount = $items->sum('amount');
                $totalOder = $items->unique('id_order')->count();

                return [
                    'channel_id' => $channelName,
                    'qty' => $totalQty,
                    'total_order' => $totalOder,
                    'amount' => $totalAmount,
                ];
            });

        return (object) [
            'qty' => $orderByDate->sum('qty'),
            'total_order' => $orderByDate->unique('id_order')->count(),
            'amount' => $orderByDate->sum('amount'),
            'recapBySalesChannel' => $recapBySalesChannel,
        ];
    }

    /**
     * Check if order have sales channel
     */
    public function checkOrderBySalesChannel(int $salesChannelId, int $tenantId): ?Order
    {
        return $this->order
            ->where('tenant_id', $tenantId)
            ->where('sales_channel_id', $salesChannelId)
            ->first();
    }
}
