<?php

namespace App\Domain\Campaign\Policies;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view campaign.
     */
    public function ViewCampaign(User $user): bool
    {
        return $user->can(PermissionEnum::ViewCampaign);
    }

    /**
     * Determine whether the user can create campaign.
     */
    public function CreateCampaign(User $user): bool
    {
        return $user->can(PermissionEnum::CreateCampaign);
    }

    /**
     * Determine whether the user can update kol.
     */
    public function UpdateCampaign(User $user, Campaign $campaign): bool
    {
        if ($user->hasRole([RoleEnum::SuperAdmin, RoleEnum::BrandManager])) {
            return true;
        }

        if ($user->can(PermissionEnum::UpdateCampaign)) {
            return $user->id === $campaign->created_by;
        };

        return false;
    }

    /**
     * Determine whether the user can update kol.
     */
    public function DeleteCampaign(User $user, Campaign $campaign): bool
    {
        if ($user->hasRole([RoleEnum::SuperAdmin, RoleEnum::BrandManager])) {
            return true;
        }

        if ($user->can(PermissionEnum::DeleteCampaign)) {
            return $user->id === $campaign->created_by;
        };

        return false;
    }
}
