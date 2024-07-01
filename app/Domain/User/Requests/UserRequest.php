<?php

namespace App\Domain\User\Requests;

use App\Domain\User\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Get the current authenticated user
        $currentUser = auth()->user();

        if ($currentUser->hasRole(RoleEnum::BrandManager)) {
            // Define the roles that should not be allowed for creation
            $forbiddenRoles = [RoleEnum::SuperAdmin, RoleEnum::BrandManager];

            // Check if any forbidden role exists in the input roles array
            if (count(array_intersect($this->input('roles', []), $forbiddenRoles)) > 0) {
                return false; // Deny authorization
            }

            $assignedTenants = $this->input('tenants', []);
            $currentUserTenant = $currentUser->tenants()->get()->pluck('id')->toArray();

            // Find the difference between assigned tenants and user's tenants
            $missingTenants = array_diff($assignedTenants, $currentUserTenant);

            // If the difference is empty, all assigned tenants exist for the user
            if (! empty($missingTenants)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'email' => ['email', 'required', 'unique:users,email'],
            'phone_number' => ['string', 'required', 'max:255', 'unique:users,phone_number'],
            'position' => ['string', 'max:255'],
            'roles' => ['required'],
            'password' => ['required', 'confirmed', 'min:6'],
        ];
    }
}
