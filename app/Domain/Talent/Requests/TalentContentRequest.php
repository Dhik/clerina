<?php

namespace App\Domain\Talent\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TalentContentRequest extends FormRequest
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
            'transfer_date'       => 'nullable|date',
            'talent_id'           => 'required|integer',
            'dealing_upload_date' => 'nullable|date',
            'posting_date'        => 'nullable|date',
            'done'                => 'required|boolean',
            'upload_link'         => 'nullable|string|max:255',
            'pic_code'            => 'nullable|string|max:255',
            'boost_code'          => 'nullable|string|max:255',
            'kerkun'              => 'required|boolean',
        ];
    }
}
