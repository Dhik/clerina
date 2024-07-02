<?php

namespace App\Domain\Campaign\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeyOpinionLeaderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view kol.
     */
    public function viewKOL(User $user): bool
    {
        return $user->can(PermissionEnum::ViewKOL);
    }

    /**
     * Determine whether the user can create kol.
     */
    public function createKOL(User $user): bool
    {
        return $user->can(PermissionEnum::ViewKOL);
    }

    /**
     * Determine whether the user can update kol.
     */
    public function updateKOL(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateKOL);
    }

    /**
     * Determine whether the user can delete kol.
     */
    public function deleteKOL(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteKOL);
    }
}
