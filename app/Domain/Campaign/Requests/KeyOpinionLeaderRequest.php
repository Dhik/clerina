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

        return [
            'channel' => ['required', Rule::in(KeyOpinionLeaderEnum::Channel)],
            'username' => ['required', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('key_opinion_leaders')->where(function ($query) {
                return $query->where('channel', $this->channel);
            })->ignore($kolId)],
            'niche' => ['required',Rule::in(KeyOpinionLeaderEnum::Niche)],
            'average_view' => ['required', 'numeric', 'integer'],
            'skin_type' => ['required', Rule::in(KeyOpinionLeaderEnum::SkinType)],
            'skin_concern' => ['required', Rule::in(KeyOpinionLeaderEnum::SkinConcern)],
            'content_type' => ['required', Rule::in(KeyOpinionLeaderEnum::ContentType)],
            'rate' => ['required', 'numeric', 'integer'],
            'pic_contact' => ['required', 'exists:users,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
