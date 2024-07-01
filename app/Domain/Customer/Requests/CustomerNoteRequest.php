<?php

namespace App\Domain\Customer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerNoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'note' => 'required|string',
            'customer_id' => 'required|exists:customers,id'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
