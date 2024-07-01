<?php

namespace App\Domain\Customer\DAL\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Order\Models\Order;
use App\DomainUtils\BaseDAL\BaseDAL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerDAL extends BaseDAL implements CustomerDALInterface
{
    public function __construct(
        protected Customer $customer,
        protected CustomerNote $customerNote
    ) {
    }

    /**
     * @return Collection
     */
    public function countOrderByPhoneNumber(): Collection
    {
        return Order::select('customer_phone_number', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('customer_phone_number')
            ->get();
    }

    /**
     * Get customer datatable
     */
    public function getCustomerDataTable(): Builder
    {
        return $this->customer->query()
            ->select('customers.id', 'customers.name', 'customers.phone_number', 'customers.count_orders', 'customers.tenant_id', 'tenants.name as tenant_name')
            ->join('tenants', 'customers.tenant_id', '=', 'tenants.id');
    }


    /**
     * Find customer by phone number
     */
    public function findCustomerByPhoneNumber(string $phoneNumber, int $tenantId): ?Customer
    {
        return $this->customer
            ->where('phone_number', $phoneNumber)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create customer
     */
    public function createCustomer(string $name, string $phoneNumber, int $tenantId): Customer
    {
        return $this->customer->create([
            'name' => $name,
            'phone_number' => $phoneNumber,
            'count_orders' => 1, // set default 1 when customer created
            'tenant_id' => $tenantId
        ]);
    }

    /**
     * Add Count order
     */
    public function addCountOrders(Customer $customer): Customer
    {
        $customer->count_orders = $customer->count_orders + 1;
        $customer->update();

        return $customer;
    }

    /**
     * Decrease Count order
     */
    public function decreaseCountOrder(Customer $customer): Customer
    {
        $customer->count_orders = $customer->count_orders - 1;
        $customer->update();

        return $customer;
    }

    /**
     * Get customer note datatable
     */
    public function getCustomerNoteDataTable(): Builder
    {
        return $this->customerNote->query()->with(['user' => function ($query) {
            $query->withoutGlobalScopes();
        }]);
    }

    /**
     * Create a new customer note
     */
    public function storeCustomerNote(array $customerNote): CustomerNote
    {
        return $this->customerNote->create($customerNote);
    }

    /**
     * Update customer note
     */
    public function updateCustomerNote(CustomerNote $customerNote, string $note): CustomerNote
    {
        $customerNote->note = $note;
        $customerNote->update();

        return $customerNote;
    }

    /**
     * Delete customer note
     */
    public function deleteCustomerNote(CustomerNote $customerNote): void
    {
        $customerNote->delete();
    }
}
