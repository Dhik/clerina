<?php

namespace App\Domain\User\Policies;

use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view user.
     */
    public function viewAnyUser(User $user): bool
    {
        return $user->can(PermissionEnum::ViewUser);
    }

    /**
     * Determine whether the user can view user.
     */
    public function viewUser(User $currentUser, User $user): bool
    {
        if ($currentUser->hasRole(RoleEnum::SuperAdmin)) {
            return true;
        }

        if ($currentUser->hasRole(RoleEnum::BrandManager)) {
            if (! $user->hasRole(RoleEnum::SuperAdmin) and ! $user->hasRole(RoleEnum::BrandManager)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create user.
     */
    public function createUser(User $user): bool
    {
        return $user->can(PermissionEnum::CreateUser);
    }

    /**
     * Determine whether the user can update user.
     */
    public function updateUser(User $currentUser, User $user): bool
    {
        if ($currentUser->hasRole(RoleEnum::SuperAdmin)) {
            return true;
        }

        if ($currentUser->hasRole(RoleEnum::BrandManager)) {
            if (! $user->hasRole(RoleEnum::SuperAdmin) and ! $user->hasRole(RoleEnum::BrandManager)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete user.
     */
    public function deleteUser(User $currentUser, User $user): bool
    {
        if ($currentUser->hasRole(RoleEnum::SuperAdmin)) {
            return true;
        }

        if ($currentUser->hasRole(RoleEnum::BrandManager)) {
            if (! $user->hasRole(RoleEnum::SuperAdmin) and ! $user->hasRole(RoleEnum::BrandManager)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can change own password.
     */
    public function changeOwnPassword(User $user): bool
    {
        return $user->can(PermissionEnum::ChangeOwnPassword);
    }

    /**
     * Determine whether the user can viewProfile.
     */
    public function viewProfile(User $user): bool
    {
        return $user->can(PermissionEnum::ViewProfile);
    }
}
