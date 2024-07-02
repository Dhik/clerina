<?php

namespace App\Domain\Customer\DAL\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Order\Models\Order;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

interface CustomerDALInterface extends BaseDALInterface
{
    /**
     * @return Collection
     */
    public function countOrderByPhoneNumber(): Collection;

    /**
     * Get customer datatable
     */
    public function getCustomerDataTable(): Builder;

    /**
     * Find customer by phone number
     */
    public function findCustomerByPhoneNumber(string $phoneNumber, int $tenantId): ?Customer;

    /**
     * Create customer
     */
    public function createCustomer(string $name, string $phoneNumber, int $tenantId): Customer;

    /**
     * Add Count order
     */
    public function addCountOrders(Customer $customer): Customer;

    /**
     * Decrease Count order
     */
    public function decreaseCountOrder(Customer $customer): Customer;

    /**
     * Get customer note datatable
     */
    public function getCustomerNoteDataTable(): Builder;

    /**
     * Create a new customer note
     */
    public function storeCustomerNote(array $customerNote): CustomerNote;

    /**
     * Update customer note
     */
    public function updateCustomerNote(CustomerNote $customerNote, string $note): CustomerNote;

    /**
     * Delete customer note
     */
    public function deleteCustomerNote(CustomerNote $customerNote): void;
}
