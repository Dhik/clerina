<?php

namespace App\Domain\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'sales_channel_id' => 'required|exists:sales_channels,id',
            'visit_amount' => 'required|numeric|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
