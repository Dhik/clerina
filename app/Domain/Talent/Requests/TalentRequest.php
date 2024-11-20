<?php

namespace App\Domain\Talent\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TalentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Adjust this based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string|max:255',
            'talent_name' => 'required|string|max:255',
            'video_slot' => 'nullable|integer',
            'content_type' => 'nullable|string|max:255',
            'produk' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'bulan_running' => 'nullable|string|max:255',
            'niche' => 'nullable|string|max:255',
            'followers' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:255',
            'no_rekening' => 'nullable|string|max:255',
            'nama_rekening' => 'nullable|string|max:255',
            'no_npwp' => 'nullable|string|max:255',
            'pengajuan_transfer_date' => 'nullable|date',
            'nik' => 'nullable|string|max:255',
            'price_rate' => 'required|string', // Ensure this is a string for money formatting
            'slot_final' => 'nullable|integer',
            'rate_final' => 'required|string', // Ensure this is a string for money formatting
            'scope_of_work' => 'nullable|string|max:255',
            'masa_kerjasama' => 'nullable|string|max:255',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'The username field is required.',
            'username.string' => 'The username must be a string.',
            'username.max' => 'The username may not be greater than 255 characters.',
            
            'talent_name.required' => 'The talent name field is required.',
            'talent_name.string' => 'The talent name must be a string.',
            'talent_name.max' => 'The talent name may not be greater than 255 characters.',
            
            'video_slot.integer' => 'The video slot must be an integer.',
            
            'content_type.string' => 'The content type must be a string.',
            'content_type.max' => 'The content type may not be greater than 255 characters.',
            
            'produk.string' => 'The product must be a string.',
            'produk.max' => 'The product may not be greater than 255 characters.',
            
            'pic.string' => 'The PIC must be a string.',
            'pic.max' => 'The PIC may not be greater than 255 characters.',
            
            'bulan_running.string' => 'The running month must be a string.',
            'bulan_running.max' => 'The running month may not be greater than 255 characters.',
            
            'niche.string' => 'The niche must be a string.',
            'niche.max' => 'The niche may not be greater than 255 characters.',
            
            'followers.integer' => 'The followers must be an integer.',
            
            'address.string' => 'The address must be a string.',
            'address.max' => 'The address may not be greater than 255 characters.',
            
            'phone_number.string' => 'The phone number must be a string.',
            'phone_number.max' => 'The phone number may not be greater than 255 characters.',
            
            'bank.string' => 'The bank must be a string.',
            'bank.max' => 'The bank may not be greater than 255 characters.',
            
            'no_rekening.string' => 'The account number must be a string.',
            'no_rekening.max' => 'The account number may not be greater than 255 characters.',
            
            'nama_rekening.string' => 'The account name must be a string.',
            'nama_rekening.max' => 'The account name may not be greater than 255 characters.',
            
            'no_npwp.string' => 'The NPWP must be a string.',
            'no_npwp.max' => 'The NPWP may not be greater than 255 characters.',
            
            'pengajuan_transfer_date.date' => 'The transfer application date must be a valid date.',
            
            'nik.string' => 'The NIK must be a string.',
            'nik.max' => 'The NIK may not be greater than 255 characters.',
            
            'price_rate.required' => 'The price rate field is required.',
            'price_rate.string' => 'The price rate must be a string.',
            
            'slot_final.integer' => 'The slot final must be an integer.',
            
            'rate_final.required' => 'The rate final field is required.',
            'rate_final.string' => 'The rate final must be a string.',
            
            'scope_of_work.string' => 'The scope of work must be a string.',
            'scope_of_work.max' => 'The scope of work may not be greater than 255 characters.',
            
            'masa_kerjasama.string' => 'The cooperation period must be a string.',
            'masa_kerjasama.max' => 'The cooperation period may not be greater than 255 characters.',
        ];
    }
}
