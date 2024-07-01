<?php

namespace App\Domain\Order\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view order.
     */
    public function viewAnyOrder(User $user): bool
    {
        return $user->can(PermissionEnum::ViewOrder);
    }

    /**
     * Determine whether the user can create order.
     */
    public function createOrder(User $user): bool
    {
        return $user->can(PermissionEnum::CreateOrder);
    }

    /**
     * Determine whether the user can update order.
     */
    public function updateOrder(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateOrder);
    }

    /**
     * Determine whether the user can delete order.
     */
    public function deleteOrder(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteOrder);
    }
}
