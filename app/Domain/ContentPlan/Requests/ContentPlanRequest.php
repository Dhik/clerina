<?php

namespace App\Domain\ContentPlan\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentPlanRequest extends FormRequest
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
            'created_date' => 'nullable|date',
            'target_posting_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
            'objektif' => 'nullable|string|max:255',
            'jenis_konten' => 'nullable|string|max:255',
            'pillar' => 'nullable|string|max:255',
            'sub_pillar' => 'nullable|string|max:255',
            'talent' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'hook' => 'nullable|string',
            'produk' => 'nullable|string|max:255',
            'referensi' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:255',
            'akun' => 'nullable|string|max:255',
            'kerkun' => 'nullable|string|max:255',
            'brief_konten' => 'nullable|string',
            'caption' => 'nullable|string',
            'link_raw_content' => 'nullable|string',
            'assignee_content_editor' => 'nullable|string|max:255',
            'link_hasil_edit' => 'nullable|string|max:255',
            'input_link_posting' => 'nullable|string|max:255',
            'posting_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'created_date' => 'Created Date',
            'target_posting_date' => 'Target Posting Date',
            'status' => 'Status',
            'objektif' => 'Objektif',
            'jenis_konten' => 'Jenis Konten',
            'pillar' => 'Pillar',
            'sub_pillar' => 'Sub Pillar',
            'talent' => 'Talent',
            'venue' => 'Venue',
            'hook' => 'Hook',
            'produk' => 'Produk',
            'referensi' => 'Referensi',
            'platform' => 'Platform',
            'akun' => 'Akun',
            'kerkun' => 'Kerkun',
            'brief_konten' => 'Brief Konten',
            'caption' => 'Caption',
            'link_raw_content' => 'Link Raw Content',
            'assignee_content_editor' => 'Assignee Content Editor',
            'link_hasil_edit' => 'Link Hasil Edit',
            'input_link_posting' => 'Input Link Posting',
            'posting_date' => 'Posting Date',
        ];
    }
}