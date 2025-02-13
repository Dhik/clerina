<?php

namespace App\Domain\AffiliateTalent\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AffiliateTalentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'gmv_bottom' => 'required|integer',
            'gmv_top' => 'required|integer',
            'contact_ig' => 'nullable|string|max:255',
            'contact_wa_notelp' => 'nullable|string|max:255',
            'contact_tiktok' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'platform_menghubungi' => 'required|string|max:255',
            'status_call' => 'required|string|max:255',
            'rate_card' => 'required|integer',
            'rate_card_final' => 'required|integer',
            'roas' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'sales_channel_id' => 'nullable|exists:sales_channels,id',
            'tenant_id' => 'nullable|exists:tenants,id',
        ];
    }
}