<?php

namespace App\Domain\Funnel\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FunnelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view funnel.
     */
    public function viewFunnel(User $user): bool
    {
        return $user->can(PermissionEnum::ViewFunnel);
    }

    /**
     * Determine whether the user can create funnel.
     */
    public function createFunnel(User $user): bool
    {
        return $user->can(PermissionEnum::CreateFunnel);
    }
}
