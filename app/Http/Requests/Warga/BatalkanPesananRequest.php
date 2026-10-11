<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class BatalkanPesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'alasan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
