<?php

namespace App\Domain\Marketing\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarketingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view marketing.
     */
    public function viewAnyMarketing(User $user): bool
    {
        return $user->can(PermissionEnum::ViewMarketing);
    }

    /**
     * Determine whether the user can create marketing.
     */
    public function createMarketing(User $user): bool
    {
        return $user->can(PermissionEnum::CreateMarketing);
    }

    /**
     * Determine whether the user can update marketing.
     */
    public function updateMarketing(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateMarketing);
    }

    /**
     * Determine whether the user can delete marketing.
     */
    public function deleteMarketing(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteMarketing);
    }
}
