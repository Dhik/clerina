<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\OfferEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceOfferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'transfer_status' => ['required', Rule::in(OfferEnum::TransferStatus)],
            'transfer_date' => ['date_format:d/m/Y', 'required_if:transfer_status,'.OfferEnum::Paid]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
