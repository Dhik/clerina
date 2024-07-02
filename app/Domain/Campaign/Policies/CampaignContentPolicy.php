<?php

namespace App\Domain\Campaign\Policies;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view campaign content.
     */
    public function ViewCampaignContent(User $user): bool
    {
        return $user->can(PermissionEnum::ViewCampaignContent);
    }

    /**
     * Determine whether the user can create campaign content.
     */
    public function CreateCampaignContent(User $user): bool
    {
        return $user->can(PermissionEnum::CreateCampaignContent);
    }

    /**
     * Determine whether the user can update campaign content.
     */
    public function UpdateCampaignContent(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateCampaignContent);
    }

    /**
     * Determine whether the user can update campaign content.
     */
    public function DeleteCampaignContent(User $user, CampaignContent $campaignContent): bool
    {
        if ($user->hasRole([RoleEnum::SuperAdmin, RoleEnum::BrandManager])) {
            return true;
        }

        if ($user->can(PermissionEnum::UpdateCampaign)) {
            return $user->id === $campaignContent->created_by;
        };

        return false;
    }
}
