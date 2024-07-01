<?php

namespace App\Domain\Customer\Policies;

use App\Domain\Customer\Models\CustomerNote;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view customer.
     */
    public function viewCustomer(User $user): bool
    {
        return $user->can(PermissionEnum::ViewCustomer);
    }
}
