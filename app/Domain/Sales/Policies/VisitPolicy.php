<?php

namespace App\Domain\Sales\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view visit.
     */
    public function viewVisit(User $user): bool
    {
        return $user->can(PermissionEnum::ViewVisit);
    }

    /**
     * Determine whether the user can create visit.
     */
    public function createVisit(User $user): bool
    {
        return $user->can(PermissionEnum::CreateVisit);
    }
}
