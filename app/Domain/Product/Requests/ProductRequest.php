<?php

namespace App\Domain\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // You can set any authorization logic here if needed.
        // For now, we will return true as it's a basic request.
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
            'product' => 'required|string|max:255',        // Product name is required, should be a string, and have a maximum length of 255 characters.
            'stock' => 'required|integer|min:0',           // Stock is required, should be an integer, and cannot be negative.
            'sku' => 'required|string|max:255',  // SKU is required, must be unique in the `products` table.
            'harga_jual' => 'required|numeric|min:0',      // Harga Jual is required and must be a numeric value greater than or equal to 0.
            'harga_markup' => 'nullable|numeric|min:0',    // Harga Markup is optional but if provided, it must be numeric and greater than or equal to 0.
            'harga_cogs' => 'nullable|numeric|min:0',      // Harga COGS is optional but if provided, it must be numeric and greater than or equal to 0.
            'harga_batas_bawah' => 'nullable|numeric|min:0', // Harga Batas Bawah is optional but if provided, it must be numeric and greater than or equal to 0.
            'tenant_id' => 'nullable|integer|exists:tenants,id', // Tenant ID is optional but if provided, it should be an integer and must exist in the `tenants` table.
        ];
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product.required' => 'Product name is required.',
            'product.string' => 'Product name must be a string.',
            'product.max' => 'Product name cannot exceed 255 characters.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be a valid integer.',
            'stock.min' => 'Stock cannot be less than 0.',
            'sku.required' => 'SKU is required.',
            'sku.string' => 'SKU must be a string.',
            'sku.max' => 'SKU cannot exceed 255 characters.',
            'harga_jual.required' => 'Harga Jual is required.',
            'harga_jual.numeric' => 'Harga Jual must be a valid number.',
            'harga_jual.min' => 'Harga Jual cannot be less than 0.',
            'harga_markup.numeric' => 'Harga Markup must be a valid number.',
            'harga_markup.min' => 'Harga Markup cannot be less than 0.',
            'harga_cogs.numeric' => 'Harga COGS must be a valid number.',
            'harga_cogs.min' => 'Harga COGS cannot be less than 0.',
            'harga_batas_bawah.numeric' => 'Harga Batas Bawah must be a valid number.',
            'harga_batas_bawah.min' => 'Harga Batas Bawah cannot be less than 0.',
            'tenant_id.integer' => 'Tenant ID must be a valid integer.',
            'tenant_id.exists' => 'Tenant ID must exist in the tenants table.',
        ];
    }
}
