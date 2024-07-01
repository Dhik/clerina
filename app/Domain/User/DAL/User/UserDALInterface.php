<?php

namespace App\Domain\User\DAL\User;

use App\Domain\User\Models\User;
use App\Domain\User\Requests\ResetPasswordRequest;
use App\Domain\User\Requests\UserRequest;
use App\Domain\User\Requests\UserUpdateRequest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface UserDALInterface extends BaseDALInterface
{
    /**
     * Return all roles and map it with enum roles
     */
    public function getAllRoles(): array|Collection;

    /**
     * Return all roles except role admin
     */
    public function getNonAdminRoles(): array|Collection;

    /**
     * Return user for DataTable
     */
    public function getUserDataTable(): Builder;

    /**
     * Get Marketing User
     */
    public function getMarketingUsers(): Collection;

    /**
     * Create new user
     */
    public function createUser(UserRequest $request): User;

    /**
     * Update user
     */
    public function updateUser(User $user, UserUpdateRequest $request): bool;

    /**
     * Delete user
     */
    public function deleteUser(User $user): void;

    /**
     * Reset password user by admin
     */
    public function resetPassword(User $user, ResetPasswordRequest $request): bool;
}
