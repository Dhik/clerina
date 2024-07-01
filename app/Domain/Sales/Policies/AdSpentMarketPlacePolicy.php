<?php

namespace App\Domain\Sales\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdSpentMarketPlacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view adSpent marketplace.
     */
    public function viewAdSpentMarketPlace(User $user): bool
    {
        return $user->can(PermissionEnum::ViewAdSpentMarketPlace);
    }

    /**
     * Determine whether the user can create adSpent marketplace.
     */
    public function createAdSpentMarketPlace(User $user): bool
    {
        return $user->can(PermissionEnum::CreateAdSpentMarketPlace);
    }
}
