<?php

namespace App\Domain\User\DAL\User;

use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use App\Domain\User\Requests\ResetPasswordRequest;
use App\Domain\User\Requests\UserRequest;
use App\Domain\User\Requests\UserUpdateRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use DragonCode\Contracts\Cashier\Auth\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserDAL extends BaseDAL implements UserDALInterface
{
    public function __construct(
        protected User $user,
        protected Role $role
    ) {
    }

    /**
     * Return all roles and map it with enum roles
     */
    public function getAllRoles(): array|Collection
    {
        return Role::all()->pluck('name', 'id')
            ->map(function ($value, $key) {
                return [
                    'id' => $value,
                    'label' => RoleEnum::getDescription($value),
                ];
            });
    }

    /**
     * Return all roles except role admin
     */
    public function getNonAdminRoles(): array|Collection
    {
        return Role::whereNot('name', RoleEnum::BrandManager)
            ->whereNot('name', RoleEnum::SuperAdmin)
            ->get()
            ->pluck('name', 'id')
            ->map(function ($value, $key) {
                return [
                    'id' => $value,
                    'label' => RoleEnum::getDescription($value),
                ];
            });
    }

    /**
     * Return user for DataTable
     */
    public function getUserDataTable(): Builder
    {
        return $this->user->query()->with('roles');
    }

    /**
     * Get Marketing User
     */
    public function getMarketingUsers(): Collection
    {
        return $this->user->whereHas('roles', function ($q) {
                $q->where('name', RoleEnum::Marketing);
            })
            ->orderBy('name', 'ASC')
            ->get();
    }


    /**
     * Create new user
     */
    public function createUser(UserRequest $request): User
    {
        return $this->user->create($request->all());
    }

    /**
     * Update user
     */
    public function updateUser(User $user, UserUpdateRequest $request): bool
    {
        return $user->update($request->all());
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    /**
     * Reset password user by admin
     */
    public function resetPassword(User $user, ResetPasswordRequest $request): bool
    {
        return $user->update([
            'password' => Hash::make($request->input('password')),
        ]);
    }
}
