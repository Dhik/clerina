<?php

namespace App\Domain\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdSpentSocialMediaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'social_media_id' => 'required|exists:social_media,id',
            'amount' => 'required|numeric|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
