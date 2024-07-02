<?php

namespace App\Domain\Employee\Policies;

use App\Domain\Employee\Models\Employee;
use App\Domain\Employee\Models\Attendance;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return boolean
     */
    public function viewEmployee(User $user): bool {
        return $user->can(PermissionEnum::ViewEmployee);
    }
    public function viewAttendance(User $user): bool {
        return $user->can(PermissionEnum::ViewAttendance);
    }
    public function accessAttendance(User $user): bool {
        return $user->can(PermissionEnum::AccessAttendance);
    }
    public function updateEmployee(User $user): bool {
        return $user->can(PermissionEnum::UpdateEmployee);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return boolean
     */
    public function view()
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return boolean
     */
    public function create()
    {
         return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return boolean
     */
    public function update()
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return boolean
     */
    public function delete()
    {
        return true;
    }
}
