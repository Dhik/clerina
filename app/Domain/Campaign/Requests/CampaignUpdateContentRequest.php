<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\CampaignContentEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignUpdateContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rate_card' => ['required', 'numeric', 'min:0'],
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
