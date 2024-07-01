<?php

namespace App\Domain\Order\BLL\Order;

use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Order\Import\OrderImport;
use App\Domain\Order\Import\OrderImportTokopedia;
use App\Domain\Order\Import\OrderImportLazada;
use App\Domain\Order\Import\OrderImportShopee;
use App\Domain\Order\Import\OrderImportTiktok;
use App\Domain\Order\Job\CreateSalesJob;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Requests\OrderStoreRequest;
use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Utilities\Request;

class OrderBLL extends BaseBLL implements OrderBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(
        protected CustomerBLLInterface $customerBLL,
        protected OrderDALInterface $orderDAL,
        protected SalesBLLInterface $salesBLL,
    ) {
    }

    /**
     * Return order for DataTable
     */
    public function getOrderDataTable(Request $request, int $tenantId): Builder
    {
        $queryOrder = $this->orderDAL->getOrderDataTable();

        $queryOrder->where('tenant_id', $tenantId);

        // Filter by phone number
        $phoneNumber = $request->input('phone_number');
        $queryOrder->when(!is_null($phoneNumber), function ($q) use ($phoneNumber) {
            $q->where('customer_phone_number', $phoneNumber);
        });

        // Filter by dates
        if (!is_null($request->input('filterDates')) && is_null($phoneNumber)) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $queryOrder->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        }

        // Filter by sales channel
        if (!is_null($request->input('filterChannel'))) {
            $queryOrder->where('sales_channel_id', $request->input('filterChannel'));
        }

        // Filter by quantity
        if (!is_null($request->input('filterQty'))) {
            $queryOrder->where('qty', $request->input('filterQty'));
        }

        // Search by SKU
        if (!is_null($request->input('filterSku'))) {
            $queryOrder->where('sku', 'like', '%' . $request->input('filterSku') . '%');
        }

        // Search by city
        if (!is_null($request->input('filterCity'))) {
            $queryOrder->where('city', 'like', '%' . $request->input('filterCity') . '%');
        }

        return $queryOrder;
    }

    /**
     * Create a new order
     * @throws Exception
     */
    public function createOrder(OrderStoreRequest $request, int $tenantId): Order
    {
        try {
            DB::beginTransaction();

            // Create order
            $orderData = $this->orderDAL->createOrder($request, $tenantId);

            // Create sales
            $this->salesBLL->createSales($orderData->date, $tenantId);

            // Create or update new customer
            $this->customerBLL->createOrUpdateCustomer($orderData->customer_name,
                $orderData->customer_phone_number,
                $orderData->tenant_id
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $orderData;
    }

    /**
     * Update an order
     * @throws Exception
     */
    public function updateOrder(Order $order, OrderStoreRequest $request): Order
    {
        try {
            DB::beginTransaction();

            // Check if phone number changed
            if ($order->customer_phone_number !== $request->input('customer_phone_number')) {
                $customer = $this->customerBLL->findCustomerByPhoneNumber($order->customer_phone_number, $order->tenant_id);
                $this->customerBLL->decreaseCountOrders($customer);
            }

            $updatedOrder = $this->orderDAL->updateOrder($order, $request);

            // Create or update new customer
            $this->customerBLL->createOrUpdateCustomer($updatedOrder->customer_name,
                $updatedOrder->customer_phone_number,
                $updatedOrder->tenant_id
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $updatedOrder;
    }

    /**
     * Delete an order
     * @throws Exception
     */
    public function deleteOrder(Order $order): void
    {
        try {
            DB::beginTransaction();

            $customer = $this->customerBLL->findCustomerByPhoneNumber($order->customer_phone_number, $order->tenant_id);
            $this->customerBLL->decreaseCountOrders($customer);
            $this->orderDAL->deleteOrder($order);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Import order
     *
     * @throws Exception
     */
    public function importOrder(Request $request, int $tenantId): void
    {
        try {
            DB::beginTransaction();
            // if ($request->input('sales_channel_id') == 1) {
            //     $import = new OrderImportShopee($request->input('sales_channel_id'), $tenantId);
            // }
            // elseif ($request->input('sales_channel_id') == 2) {
            //     $import = new OrderImportLazada($request->input('sales_channel_id'), $tenantId);
            // }
            // elseif ($request->input('sales_channel_id') == 3) {
            //     $import = new OrderImportTokopedia($request->input('sales_channel_id'), $tenantId);
            // }
            // elseif ($request->input('sales_channel_id') == 4) {
            //     $import = new OrderImportTiktok($request->input('sales_channel_id'), $tenantId);
            // }
            // else {
            $import = new OrderImport($request->input('sales_channel_id'), $tenantId);
            // }
            
            Excel::import($import, $request->file('fileOrderImport'));

            $importedData = $import->getImportedData();
            if (! empty($importedData)) {
                $dates = array_column($importedData, 'date');
                $uniqueDates = array_unique($dates);

                foreach ($uniqueDates as $date) {
                    CreateSalesJob::dispatch($date, $tenantId);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
