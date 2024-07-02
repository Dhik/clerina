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
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface TenantDALInterface extends BaseDALInterface
{
    /**
     * Return tenant for DataTable
     */
    public function getTenantDataTable(): Builder;

    /**
     * Return all tenant
     */
    public function getAllTenants(): Collection;

    /**
     * Change active tenant user
     */
    public function changeTenantUser(int $tenantId): void;

    /**
     * Create a new tenant
     */
    public function storeTenant(TenantRequest $request): Tenant;

    /**
     * Update tenant
     */
    public function updateTenant(Tenant $tenant, TenantRequest $request): Tenant;

    /**
     * Delete Tenant
     */
    public function deleteTenant(Tenant $tenant): void;

    /**
     * Check if tenant is used by user
     */
    public function checkCurrentTenantUser(int $tenantId): ?User;

    /**
     * Check if tenant is used by order
     */
    public function checkOrderTenant(int $tenantId): ?Order;

    /**
     * Check if tenant is used by sales
     */
    public function checkSalesTenant(int $tenantId): ?Sales;

    /**
     * Check if tenant is used by visit
     */
    public function checkVisitTenant(int $tenantId): ?Visit;

    /**
     * Check if tenant is used by marketing
     */
    public function checkMarketingTenant(int $tenantId): ?Marketing;

    /**
     * Check if tenant is used by Ad spent marketplace
     */
    public function checkAdSpentMPTenant(int $tenantId): ?AdSpentMarketPlace;

    /**
     * Check if tenant is used by Ad spent social media
     */
    public function checkAdSpentSMTenant(int $tenantId): ?AdSpentSocialMedia;
}
