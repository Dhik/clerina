<?php

namespace App\Domain\Funnel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFunnelTofuRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'spend' => 'nullable|numeric',
            'reach' => 'nullable|numeric',
            'impression' => 'nullable|numeric',
            'cpv' => 'nullable|numeric',
            'play_video' => 'nullable|numeric',
            'link_click' => 'nullable|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
