<?php

namespace App\Domain\User\BLL\User;

use App\Domain\User\Models\User;
use App\Domain\User\Requests\ResetPasswordRequest;
use App\Domain\User\Requests\UserRequest;
use App\Domain\User\Requests\UserUpdateRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface UserBLLInterface extends BaseBLLInterface
{
    /**
     * Return all roles and map it with enum roles
     */
    public function getAllRoles(): array|Collection;

    /**
     * Return user for DataTable
     */
    public function getUserDataTable(): Builder;

    /**
     * Return marketing user
     */
    public function getMarketingUsers(): Collection;

    /**
     * Create new user
     */
    public function createUser(UserRequest $request): User;

    /**
     * Update user
     */
    public function updateUser(User $user, UserUpdateRequest $request): User;

    /**
     * Delete user
     */
    public function deleteUser(User $user): void;

    /**
     * Get roles user and transform it via enum to get label
     */
    public function getRoleUser(User $user): string;

    /**
     * Reset password user by admin
     */
    public function resetPassword(User $user, ResetPasswordRequest $request): bool;
}
