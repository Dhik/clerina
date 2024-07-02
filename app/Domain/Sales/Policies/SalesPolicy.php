<?php

namespace App\Domain\Sales\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view sales.
     */
    public function viewAnySales(User $user): bool
    {
        return $user->can(PermissionEnum::ViewSales);
    }

    /**
     * Determine whether the user can view sales.
     */
    public function viewSales(User $user): bool
    {
        return $user->can(PermissionEnum::ViewSales);
    }

    /**
     * Determine whether the user can create sales.
     */
    public function createSales(User $user): bool
    {
        return $user->can(PermissionEnum::CreateSales);
    }

    /**
     * Determine whether the user can update sales.
     */
    public function updateSales(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateSales);
    }

    /**
     * Determine whether the user can delete sales.
     */
    public function deleteSales(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteSales);
    }
}
