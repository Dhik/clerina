<?php

namespace App\Domain\User\BLL\User;

use App\Domain\User\DAL\User\UserDALInterface;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use App\Domain\User\Requests\ResetPasswordRequest;
use App\Domain\User\Requests\UserRequest;
use App\Domain\User\Requests\UserUpdateRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserBLL extends BaseBLL implements UserBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(protected UserDALInterface $userDAL)
    {
    }

    /**
     * Return all roles and map it with enum roles
     */
    public function getAllRoles(): array|Collection
    {
        if (Auth::user()->hasRole(RoleEnum::SuperAdmin)) {
            return $this->userDAL->getAllRoles();
        }

        return $this->userDAL->getNonAdminRoles();
    }

    /**
     * Return user for DataTable
     */
    public function getUserDataTable(): Builder
{
    $query = $this->userDAL->getUserDataTable();
    return $query;
}



    /**
     * Return marketing user
     */
    public function getMarketingUsers(): Collection
    {
        return $this->userDAL->getMarketingUsers();
    }

    /**
     * Create new user
     */
    public function createUser(UserRequest $request): User
    {
        $request->password = Hash::make($request->password);

        $user = $this->userDAL->createUser($request);
        $user->syncRoles($request->input('roles'));
        $user->tenants()->sync($request->input('tenants'));

        return $user;
    }

    /**
     * Update user
     */
    public function updateUser(User $user, UserUpdateRequest $request): User
    {
        $this->userDAL->updateUser($user, $request);
        $user->syncRoles($request->input('roles'));
        $user->tenants()->sync($request->input('tenants'));

        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): void
    {
        $this->userDAL->deleteUser($user);
    }

    /**
     * Get roles user and transform it via enum to get label
     */
    public function getRoleUser(User $user): string
    {
        return $user->roles->pluck('name')->map(function ($value, $key) {
            return RoleEnum::getDescription($value);
        })->implode(', ');
    }

    /**
     * Reset password user by admin
     */
    public function resetPassword(User $user, ResetPasswordRequest $request): bool
    {
        return $this->userDAL->resetPassword($user, $request);
    }
}
