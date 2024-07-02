<?php

namespace App\Domain\Marketing\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocialMediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view social media.
     */
    public function viewAnySocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::ViewSocialMedia);
    }

    /**
     * Determine whether the user can view social media.
     */
    public function viewSocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::ViewSocialMedia);
    }

    /**
     * Determine whether the user can create social media.
     */
    public function createSocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::CreateSocialMedia);
    }

    /**
     * Determine whether the user can update social media.
     */
    public function updateSocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateSocialMedia);
    }

    /**
     * Determine whether the user can delete social media.
     */
    public function deleteSocialMedia(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteSocialMedia);
    }
}
