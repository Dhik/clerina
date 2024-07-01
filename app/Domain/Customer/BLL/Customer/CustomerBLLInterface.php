<?php

namespace App\Domain\Customer\BLL\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Customer\Requests\CustomerNoteRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\Utilities\Request;

interface CustomerBLLInterface extends BaseBLLInterface
{
    /**
     * @return Collection
     */
    public function countOrderByPhoneNumber(): Collection;

    /**
     * Return customer note for DataTable
     */
    public function getCustomerDatatable(Request $request, int $tenant_id): Builder;

    /**
     * Find customer by phone number
     */
    public function findCustomerByPhoneNumber(string $phoneNumber, int $tenant_id): ?Customer;

    /**
     * Create or update customer
     */
    public function createOrUpdateCustomer(string $name, string $phoneNumber, string $tenantId): Customer;

    /**
     * Add order count
     */
    public function addCountOrders(Customer $customer): void;

    /**
     * Decrease order count
     */
    public function decreaseCountOrders(Customer $customer): void;

    /**
     * Return customer note for DataTable
     */
    public function getCustomerNoteDatatable(Request $request): Builder;

    /**
     * Create a new customer note
     */
    public function storeCustomerNote(CustomerNoteRequest $request, int $userId): CustomerNote;

    /**
     * Update customer note
     */
    public function updateCustomerNote(CustomerNote $customerNote, CustomerNoteRequest $request): CustomerNote;

    /**
     * Delete customer note
     */
    public function deleteCustomerNote(CustomerNote $customerNote): void;
}
