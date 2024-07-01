<?php

namespace App\Domain\Tenant\DAL\Tenant;

use App\Domain\Marketing\Models\Marketing;
use App\Domain\Order\Models\Order;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\Visit;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Domain\User\Models\User;
use App\DomainUtils\BaseDAL\BaseDAL;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TenantDAL extends BaseDAL implements TenantDALInterface
{
    public function __construct(
        protected Tenant $tenant,
        protected User $user,
        protected Order $order,
        protected Sales $sales,
        protected Marketing $marketing,
        protected Visit $visit,
        protected AdSpentMarketPlace $adSpentMarketPlace,
        protected AdSpentSocialMedia $adSpentSocialMedia
    ) {
    }

    /**
     * Return tenant for DataTable
     */
    public function getTenantDataTable(): Builder
    {
        return $this->tenant->query();
    }

    /**
     * Return all tenant
     */
    public function getAllTenants(): Collection
    {
        return $this->tenant->all();
    }

    /**
     * Change active tenant user
     */
    public function changeTenantUser(int $tenantId): void
    {
        Auth::user()->update(['current_tenant_id' => $tenantId]);
    }

    /**
     * Create a new tenant
     */
    public function storeTenant(TenantRequest $request): Tenant
    {
        return $this->tenant->create($request->only('name'));
    }

    /**
     * Update tenant
     */
    public function updateTenant(Tenant $tenant, TenantRequest $request): Tenant
    {
        $tenant->name = $request->name;
        $tenant->update();

        return $tenant;
    }

    /**
     * Delete Tenant
     */
    public function deleteTenant(Tenant $tenant): void
    {
        $tenant->delete();
    }

    /**
     * Check if tenant is used by user
     */
    public function checkCurrentTenantUser(int $tenantId): ?User
    {
        return $this->user->where('current_tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant is used by order
     */
    public function checkOrderTenant(int $tenantId): ?Order
    {
        return $this->order->where('tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant is used by sales
     */
    public function checkSalesTenant(int $tenantId): ?Sales
    {
        return $this->sales->where('tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant is used by visit
     */
    public function checkVisitTenant(int $tenantId): ?Visit
    {
        return $this->visit->where('tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant is used by marketing
     */
    public function checkMarketingTenant(int $tenantId): ?Marketing
    {
        return $this->marketing->where('tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant is used by Ad spent marketplace
     */
    public function checkAdSpentMPTenant(int $tenantId): ?AdSpentMarketPlace
    {
        return $this->adSpentMarketPlace->where('tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant is used by Ad spent social media
     */
    public function checkAdSpentSMTenant(int $tenantId): ?AdSpentSocialMedia
    {
        return $this->adSpentSocialMedia->where('tenant_id', $tenantId)->first();
    }
}
