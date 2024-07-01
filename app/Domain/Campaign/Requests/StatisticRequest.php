<?php

namespace App\Domain\Campaign\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatisticRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'like' => ['nullable', 'numeric'],
            'view' => ['nullable', 'numeric'],
            'comment' => ['nullable', 'numeric']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
