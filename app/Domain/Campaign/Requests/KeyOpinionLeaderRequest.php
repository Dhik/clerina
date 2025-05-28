<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\KeyOpinionLeaderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KeyOpinionLeaderRequest extends FormRequest
{
    public function rules(): array
    {
        $kolId = $this->route('keyOpinionLeader');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'channel' => $isUpdate ? ['nullable'] : ['required', Rule::in(KeyOpinionLeaderEnum::Channel)],
            'username' => ['required', 'regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('key_opinion_leaders')->where(function ($query) {
                // For updates, get the current channel; for creates, use the input channel
                if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                    $kol = $this->route('keyOpinionLeader');
                    return $query->where('channel', $kol->channel);
                }
                return $query->where('channel', $this->channel);
            })->ignore($kolId)],
            'niche' => $isUpdate ? ['nullable'] : ['required', Rule::in(KeyOpinionLeaderEnum::Niche)],
            'average_view' => $isUpdate ? ['nullable'] : ['required', 'numeric', 'integer'],
            'skin_type' => $isUpdate ? ['nullable'] : ['required', Rule::in(KeyOpinionLeaderEnum::SkinType)],
            'skin_concern' => $isUpdate ? ['nullable'] : ['required', Rule::in(KeyOpinionLeaderEnum::SkinConcern)],
            'content_type' => $isUpdate ? ['nullable'] : ['required', Rule::in(KeyOpinionLeaderEnum::ContentType)],
            'rate' => $isUpdate ? ['nullable'] : ['required', 'numeric', 'integer'],
            'pic_contact' => $isUpdate ? ['nullable'] : ['required', 'exists:users,id'],
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