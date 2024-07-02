<?php

namespace App\Domain\Tenant\BLL\Tenant;

use App\Domain\Tenant\DAL\Tenant\TenantDALInterface;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Domain\User\Enums\RoleEnum;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property TenantDALInterface DAL
 */
class TenantBLL extends BaseBLL implements TenantBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(protected TenantDALInterface $tenantDAL)
    {
    }

    /**
     * Return tenant for DataTable
     */
    public function getTenantDataTable(): Builder
    {
        return $this->tenantDAL->getTenantDataTable();
    }

    /**
     * Return all tenants
     */
    public function getAllTenants(): Collection
    {
        if (Auth::user()->hasRole(RoleEnum::SuperAdmin)) {
            return $this->tenantDAL->getAllTenants();
        }

        return Auth::user()->tenants()->get();
    }

    /**
     * Change active tenant user
     */
    public function changeTenant(int $tenantId): void
    {
        // check if user have role other than super admin
        if (! Auth::user()->hasRole(RoleEnum::SuperAdmin)) {
            Auth::user()->tenants()->findOrFail($tenantId);
        }

        $this->tenantDAL->changeTenantUser($tenantId);
    }

    /**
     * Change default tenant user
     */
    public function setDefaultTenant(): void
    {
        if (is_null(Auth()->user()->current_tenant_id)) {

            // check if user have role other than super admin
            if (Auth::user()->hasRole(RoleEnum::SuperAdmin)) {
                $tenant = $this->tenantDAL->getAllTenants()->first();
            } else {
                $tenant = Auth::user()->tenants()->first();
            }

            if (! is_null($tenant)) {
                $this->tenantDAL->changeTenantUser($tenant->id);
            }
        }
    }

    /**
     * Create new tenant
     */
    public function storeTenant(TenantRequest $request): Tenant
    {
        return $this->tenantDAL->storeTenant($request);
    }

    /**
     * Update tenant
     */
    public function updateTenant(Tenant $tenant, TenantRequest $request): Tenant
    {
        return $this->tenantDAL->updateTenant($tenant, $request);
    }

    /**
     * Delete tenant
     */
    public function deleteTenant(Tenant $tenant): bool
    {
        $checkCurrentTenantUser = $this->tenantDAL->checkCurrentTenantUser($tenant->id);

        if (! empty($checkCurrentTenantUser)) {
            return false;
        }

        $checkOrderTenant = $this->tenantDAL->checkOrderTenant($tenant->id);

        if (! empty($checkOrderTenant)) {
            return false;
        }

        $checkSalesTenant = $this->tenantDAL->checkSalesTenant($tenant->id);

        if (! empty($checkSalesTenant)) {
            return false;
        }

        $checkVisitTenant = $this->tenantDAL->checkVisitTenant($tenant->id);

        if (! empty($checkVisitTenant)) {
            return false;
        }

        $checkMarketingTenant = $this->tenantDAL->checkMarketingTenant($tenant->id);

        if (! empty($checkMarketingTenant)) {
            return false;
        }

        $checkAdSpentMPTenant = $this->tenantDAL->checkAdSpentMPTenant($tenant->id);

        if (! empty($checkAdSpentMPTenant)) {
            return false;
        }

        $checkAdSpentSMTenant = $this->tenantDAL->checkAdSpentSMTenant($tenant->id);

        if (! empty($checkAdSpentSMTenant)) {
            return false;
        }

        $this->tenantDAL->deleteTenant($tenant);

        return true;
    }
}
