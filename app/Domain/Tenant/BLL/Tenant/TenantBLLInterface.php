<?php

namespace App\Domain\Tenant\BLL\Tenant;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface TenantBLLInterface extends BaseBLLInterface
{
    /**
     * Return tenant for DataTable
     */
    public function getTenantDataTable(): Builder;

    /**
     * Return all tenants
     */
    public function getAllTenants(): Collection;

    /**
     * Change active tenant user
     */
    public function changeTenant(int $tenantId): void;

    /**
     * Change default tenant user
     */
    public function setDefaultTenant(): void;

    /**
     * Create new tenant
     */
    public function storeTenant(TenantRequest $request): Tenant;

    /**
     * Update tenant
     */
    public function updateTenant(Tenant $tenant, TenantRequest $request): Tenant;

    /**
     * Delete tenant
     */
    public function deleteTenant(Tenant $tenant): bool;
}
