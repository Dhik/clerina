<?php

namespace App\Domain\Customer\Policies;

use App\Domain\Customer\Models\CustomerNote;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create customer note.
     */
    public function createCustomerNote(User $user): bool
    {
        return $user->can(PermissionEnum::CreateCustomerNote);
    }

    /**
     * Determine whether the user can update customer note.
     */
    public function updateCustomerNote(User $user, CustomerNote $customerNote): bool
    {
        if ($user->hasRole(RoleEnum::SuperAdmin) || $user->has(RoleEnum::BrandManager)) {
            return true;
        }

        if ($user->can(PermissionEnum::UpdateCustomerNote)) {
            if (!$customerNote->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete customer note.
     */
    public function deleteCustomerNote(User $user, CustomerNote $customerNote): bool
    {
        if ($user->hasRole(RoleEnum::SuperAdmin) || $user->has(RoleEnum::BrandManager)) {
            return true;
        }

        if ($user->can(PermissionEnum::DeleteCustomerNote)) {
            if (!$customerNote->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }
}
