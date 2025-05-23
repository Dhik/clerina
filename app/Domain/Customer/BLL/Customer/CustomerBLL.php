<?php

namespace App\Domain\Customer\BLL\Customer;

use App\Domain\Customer\DAL\Customer\CustomerDALInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Customer\Requests\CustomerNoteRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Utilities\Request;

class CustomerBLL extends BaseBLL implements CustomerBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(protected CustomerDALInterface $customerDAL)
    {}

    /**
     * @return Collection
     */
    public function countOrderByPhoneNumber(): Collection
    {
        return $this->customerDAL->countOrderByPhoneNumber();
    }

    /**
     * Return customer note for DataTable
     */
    // In CustomerBLL
    public function getCustomerDatatable(Request $request, int $tenant_id): Builder
    {
        $query = $this->customerDAL->getCustomerDataTable();
        
        // Apply tenant filter (from authenticated user)
        $query->where('customers.tenant_id', $tenant_id);

        // Apply month filter if provided, otherwise default to current month
        if ($request->has('filterMonth') && !empty($request->input('filterMonth'))) {
            $date = explode('-', $request->input('filterMonth'));
            $year = $date[0];
            $month = $date[1];
            
            $query->whereYear('customers.last_order_date', '=', $year)
                ->whereMonth('customers.last_order_date', '=', $month);
        } else {
            // Default to current month if no month filter is provided
            $query->whereYear('customers.last_order_date', '=', now()->year)
                ->whereMonth('customers.last_order_date', '=', now()->month);
        }
        
        // Apply customer type filter if provided
        if ($request->has('filterType') && !empty($request->input('filterType'))) {
            $query->where('customers.type', $request->input('filterType'));
        }
        
        // Apply count_orders filter if provided
        if ($request->has('filterCountOrders') && !empty($request->input('filterCountOrders'))) {
            $query->where('customers.count_orders', '=', $request->input('filterCountOrders'));
        }

        return $query;
    }



    /**
     * Find customer by phone number
     */
    public function findCustomerByPhoneNumber(string $phoneNumber, int $tenant_id): ?Customer
    {
        return $this->customerDAL->findCustomerByPhoneNumber($phoneNumber, $tenant_id);
    }

    /**
     * Create or update customer
     */
    public function createOrUpdateCustomer(string $name, string $phoneNumber, string $tenantId): Customer
    {
        $checkIfCustomerExist = $this->customerDAL->findCustomerByPhoneNumber($phoneNumber, $tenantId);

        if (!$checkIfCustomerExist) {
            $customer = $this->customerDAL->createCustomer($name, $phoneNumber, $tenantId);
        } else {
            $customer = $this->customerDAL->addCountOrders($checkIfCustomerExist);
        }
        return $customer;
    }

    /**
     * Add order count
     */
    public function addCountOrders(Customer $customer): void
    {
        $this->customerDAL->addCountOrders($customer);
    }

    /**
     * Decrease order count
     */
    public function decreaseCountOrders(Customer $customer): void
    {
        $this->customerDAL->decreaseCountOrder($customer);
    }

    /**
     * Return customer note for DataTable
     */
    public function getCustomerNoteDatatable(Request $request): Builder
    {
        $query = $this->customerDAL->getCustomerNoteDataTable();

        $query->where('customer_id', $request->input('customer_id'));

        return $query;
    }

    /**
     * Create a new customer note
     */
    public function storeCustomerNote(CustomerNoteRequest $request, int $userId): CustomerNote
    {
        $data = [
            'note' => $request->input('note'),
            'customer_id' => $request->input('customer_id'),
            'user_id' => $userId
        ];

        return $this->customerDAL->storeCustomerNote($data);
    }

    /**
     * Update customer note
     */
    public function updateCustomerNote(CustomerNote $customerNote, CustomerNoteRequest $request): CustomerNote
    {
        return $this->customerDAL->updateCustomerNote($customerNote, $request->input('note'));
    }

    /**
     * Delete customer note
     */
    public function deleteCustomerNote(CustomerNote $customerNote): void
    {
        $this->customerDAL->deleteCustomerNote($customerNote);
    }
}
