<?php

namespace App\Domain\Contest\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view campaign.
     */
    public function ViewContest(User $user): bool
    {
        return $user->can(PermissionEnum::ViewContest);
    }

    /**
     * Determine whether the user can create campaign.
     */
    public function CreateContest(User $user): bool
    {
        return $user->can(PermissionEnum::CreateContest);
    }

    /**
     * Determine whether the user can update kol.
     */
    public function UpdateContest(User $user): bool
    {
        return $user->can(PermissionEnum::UpdateContest);
    }

    /**
     * Determine whether the user can update kol.
     */
    public function DeleteContest(User $user): bool
    {
        return $user->can(PermissionEnum::DeleteContest);
    }
}
