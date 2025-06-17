<?php

namespace App\Domain\ContentAds\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\ContentAds\Models\ContentAds;

class ContentAdsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'link_ref' => 'nullable|string|max:255',
            'desc_request' => 'nullable|string',
            'product' => 'nullable|string|in:' . implode(',', array_keys(ContentAds::getProductOptions())),
            'platform' => 'nullable|string|in:' . implode(',', array_keys(ContentAds::getPlatformOptions())),
            'funneling' => 'nullable|string|in:' . implode(',', array_keys(ContentAds::getFunnelingOptions())),
            'request_date' => 'nullable|date',
            'link_drive' => 'nullable|string|max:255',
            'editor' => 'nullable|string|in:RAFI,HENDRA',
            'status' => 'nullable|string|max:255',
            'filename' => 'nullable|string|max:255',
            'tugas_selesai' => 'nullable|boolean',
            'assignee_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'link_ref' => 'Link Reference',
            'desc_request' => 'Description Request',
            'product' => 'Product',
            'platform' => 'Platform',
            'funneling' => 'Funneling',
            'request_date' => 'Request Date',
            'link_drive' => 'Link Drive',
            'editor' => 'Editor',
            'status' => 'Status',
            'filename' => 'File Name',
            'tugas_selesai' => 'Task Completed',
            'assignee_id' => 'Assignee',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product.in' => 'The selected product is invalid. Please choose from the available options.',
            'platform.in' => 'The selected platform is invalid. Please choose from META or TIKTOK.',
            'funneling.in' => 'The selected funneling is invalid. Please choose from TOFU, MOFU, BOFU, or None.',
            'editor.in' => 'The selected editor is invalid. Please choose from RAFI or HENDRA.',
            'assignee_id.exists' => 'The selected assignee does not exist.',
        ];
    }
}