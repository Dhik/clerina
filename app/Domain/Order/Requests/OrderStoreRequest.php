<?php

namespace App\Domain\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'id_order' => 'required',
            'sales_channel_id' => 'required|exists:sales_channels,id',
            'customer_name' => 'required|max:255',
            'customer_phone_number' => 'required|max:255',
            'product' => 'max:255',
            'qty' => 'required|numeric|integer',
            'receipt_number' => 'required',
            'sku' => 'required',
            'price' => 'required|numeric|integer',
            'shipping_address' => 'required',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
