<?php

namespace App\Domain\Campaign\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatProofRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Adjust mime types and max file size as needed
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
