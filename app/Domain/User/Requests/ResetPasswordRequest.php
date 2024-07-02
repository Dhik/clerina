<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => 'required|min:6|confirmed',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
