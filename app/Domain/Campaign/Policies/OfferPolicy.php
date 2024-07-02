<?php

namespace App\Domain\Campaign\Policies;

use App\Domain\Campaign\Models\Offer;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfferPolicy
{
    use HandlesAuthorization;

    public function viewOffer(User $user): bool
    {
        return $user->can(PermissionEnum::ViewOffer);
    }

    public function createOffer(User $user): bool
    {
        return $user->can(PermissionEnum::CreateOffer);
    }

    public function updateOffer(User $user, Offer $offer): bool
    {
        if ($user->hasRole([RoleEnum::SuperAdmin, RoleEnum::BrandManager])) {
            return true;
        }

        if ($user->can(PermissionEnum::UpdateOffer)) {
            return $user->id === $offer->created_by;
        };

        return false;
    }

    public function approveRejectOffer(User $user): bool
    {
        return $user->can(PermissionEnum::ApproveRejectOffer);
    }

    public function reviewOffer(User $user, Offer $offer): bool
    {
        if ($user->hasRole([RoleEnum::SuperAdmin, RoleEnum::BrandManager])) {
            return true;
        }

        if ($user->can(PermissionEnum::ReviewOffer)) {
            return $user->id === $offer->created_by;
        };

        return false;
    }

    public function financeOffer(User $user): bool
    {
        return $user->can(PermissionEnum::FinanceOffer);
    }
}
