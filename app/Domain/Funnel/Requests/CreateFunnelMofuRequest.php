<?php

namespace App\Domain\Funnel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFunnelMofuRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'spend' => 'nullable|numeric',
            'reach' => 'nullable|numeric',
            'impression' => 'nullable|numeric',
            'engagement' => 'nullable|numeric',
            'link_click' => 'nullable|numeric',
            'ctr' => 'nullable|numeric',
            'cplv' => 'nullable|numeric',
            'cpa' => 'nullable|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
