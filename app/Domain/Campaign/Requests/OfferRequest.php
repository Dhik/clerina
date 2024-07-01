<?php

namespace App\Domain\Campaign\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key_opinion_leader_id' => ['required', 'integer', 'exists:key_opinion_leaders,id'],
            'rate_per_slot' => ['required', 'numeric'],
            'benefit' => ['required', 'string'],
            'negotiate' => ['required', 'string']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
