<?php

namespace App\Domain\Sales\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdSpentSocialMediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view adSpent social media.
     */
    public function viewAdSpentSocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::ViewAdSpentSocialMedia);
    }

    /**
     * Determine whether the user can create adSpent social media.
     */
    public function createAdSpentSocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::CreateAdSpentSocialMedia);
    }
}
