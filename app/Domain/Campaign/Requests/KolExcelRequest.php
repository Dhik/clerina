<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\KeyOpinionLeaderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KolExcelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'data.*.0' => ['required', Rule::in(KeyOpinionLeaderEnum::Channel)],
            'data.*.1' => ['required', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('key_opinion_leaders', 'username')->where(function ($query) {
                return $query->where('channel', $this->input('data.*.0'));
            })],
            'data.*.2' => ['required',Rule::in(KeyOpinionLeaderEnum::Niche)],
            'data.*.3' => ['required', 'numeric', 'integer'],
            'data.*.4' => ['required', Rule::in(KeyOpinionLeaderEnum::SkinType)],
            'data.*.5' => ['required', Rule::in(KeyOpinionLeaderEnum::SkinConcern)],
            'data.*.6' => ['required', Rule::in(KeyOpinionLeaderEnum::ContentType)],
            'data.*.7' => ['required', 'numeric', 'integer'],
            'data.*.8' => ['required', 'exists:users,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [
            'data.*.0.required' => 'Data pada baris :position, Kolom Channel harus diisi.',
            'data.*.1.required' => 'Data pada baris :position, Kolom Username harus diisi.',
            'data.*.1.regex' => 'Data pada baris :position, Kolom Username hanya boleh berisi huruf, angka, dan garis bawah.',
            'data.*.1.unique' => 'Data pada baris :position, Username sudah ada untuk channel ini.',
            'data.*.2.required' => 'Data pada baris :position, Kolom Niche harus diisi.',
            'data.*.3.required' => 'Data pada baris :position, Kolom Average view harus diisi.',
            'data.*.3.numeric' => 'Data pada baris :position, Kolom Average view harus berupa angka.',
            'data.*.3.integer' => 'Data pada baris :position, Kolom Average view harus berupa bilangan bulat.',
            'data.*.4.required' => 'Data pada baris :position, Kolom Skin type harus diisi.',
            'data.*.5.required' => 'Data pada baris :position, Kolom Skin concern harus diisi.',
            'data.*.6.required' => 'Data pada baris :position, Kolom Jenis Konten harus diisi.',
            'data.*.7.required' => 'Data pada baris :position, Kolom Rate harus diisi.',
            'data.*.7.numeric' => 'Data pada baris :position, Kolom Rate harus berupa angka.',
            'data.*.7.integer' => 'Data pada baris :position, Kolom Rate harus berupa bilangan bulat.',
            'data.*.8.required' => 'Data pada baris :position, Kolom PIC harus diisi.',
            'data.*.8.exists' => 'Data pada baris :position, Kolom PIC tidak ditemukan.'
        ];
    }
}
