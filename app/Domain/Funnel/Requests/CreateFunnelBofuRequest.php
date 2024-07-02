<?php

namespace App\Domain\Funnel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFunnelBofuRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'spend' => 'nullable|numeric',
            'atc' => 'nullable|numeric',
            'initiated_checkout_number' => 'nullable|numeric',
            'purchase_number' => 'nullable|numeric',
            'roas' => 'nullable|numeric',
            'frequency' => 'nullable|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
