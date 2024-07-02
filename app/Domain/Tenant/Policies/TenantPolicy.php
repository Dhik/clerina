<?php

namespace App\Domain\Tenant\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view tenant.
     */
    public function viewTenant(User $user): bool
    {
        return $user->can(PermissionEnum::ViewTenant);
    }

    /**
     * Determine whether the user can create tenant.
     */
    public function createTenant(User $user): bool
    {
        return $user->can(PermissionEnum::CreateTenant);
    }

    /**
     * Determine whether the user can update tenant.
     */
    public function updateTenant(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateTenant);
    }

    /**
     * Determine whether the user can delete tenant.
     */
    public function deleteTenant(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteTenant);
    }
}
