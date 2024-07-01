<?php

namespace App\Domain\Marketing\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarketingCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view marketing category.
     */
    public function viewAnyMarketingCategory(User $user): bool
    {
        return $user->can(PermissionEnum::ViewMarketingCategory);
    }

    /**
     * Determine whether the user can view marketing category.
     */
    public function viewMarketingCategory(User $user): bool
    {
        return $user->can(PermissionEnum::ViewMarketingCategory);
    }

    /**
     * Determine whether the user can create marketing category.
     */
    public function createMarketingCategory(User $user): bool
    {
        return $user->can(PermissionEnum::CreateMarketingCategory);
    }

    /**
     * Determine whether the user can update marketing category.
     */
    public function updateMarketingCategory(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateMarketingCategory);
    }

    /**
     * Determine whether the user can delete marketing category.
     */
    public function deleteMarketingCategory(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteMarketingCategory);
    }
}
