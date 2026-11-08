<?php

namespace App\Http\Requests\Sopir;

use Illuminate\Foundation\Http\FormRequest;

class OnlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'online' => ['required', 'boolean'],
        ];
    }
}
