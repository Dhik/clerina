<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\CampaignContentEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key_opinion_leader_id' => ['integer', 'exists:key_opinion_leaders,id'],
            'rate_card' => ['required', 'numeric'],
            'task_name' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'url'],
            'product' => ['required', 'string', 'max:255'],
            'channel' => ['required', Rule::in(CampaignContentEnum::PlatformValidation)]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
