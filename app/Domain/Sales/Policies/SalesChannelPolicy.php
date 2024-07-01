<?php

namespace App\Domain\Sales\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesChannelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view sales channel.
     */
    public function viewAnySalesChannel(User $user): bool
    {
        return $user->can(PermissionEnum::ViewSalesChannel);
    }

    /**
     * Determine whether the user can view sales channel.
     */
    public function viewSalesChannel(User $user): bool
    {
        return $user->can(PermissionEnum::ViewSalesChannel);
    }

    /**
     * Determine whether the user can create sales channel.
     */
    public function createSalesChannel(User $user): bool
    {
        return $user->can(PermissionEnum::CreateSalesChannel);
    }

    /**
     * Determine whether the user can update sales channel.
     */
    public function updateSalesChannel(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateSalesChannel);
    }

    /**
     * Determine whether the user can delete sales channel.
     */
    public function deleteSalesChannel(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteSalesChannel);
    }
}
