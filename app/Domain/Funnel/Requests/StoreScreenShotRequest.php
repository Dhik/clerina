<?php

namespace App\Domain\Funnel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScreenShotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
