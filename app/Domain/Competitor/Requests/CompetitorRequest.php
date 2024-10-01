<?php

namespace App\Domain\Competitor\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompetitorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'brand' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'channel' => 'nullable|string|max:255',
            'omset' => 'nullable|integer',
            'periode' => 'nullable|date',
            'type' => 'nullable|string|max:255',
        ];
    }
}
