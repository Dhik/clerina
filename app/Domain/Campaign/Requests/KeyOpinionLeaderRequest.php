<?php

namespace App\Domain\Campaign\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KeyOpinionLeaderEditRequest extends FormRequest
{
    public function rules(): array
    {
        $kolId = $this->route('keyOpinionLeader');

        return [
            'username' => ['required', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('key_opinion_leaders')->where(function ($query) {
                // Get the current KOL to check its channel for uniqueness
                $kol = $this->route('keyOpinionLeader');
                return $query->where('channel', $kol->channel);
            })->ignore($kolId)],
            'phone_number' => ['nullable', 'string'],
            'views_last_9_post' => ['nullable', 'boolean'],
            'activity_posting' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}