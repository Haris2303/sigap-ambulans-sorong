<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TugaskanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'ambulans_id' => ['required', 'integer', 'exists:ambulans,id'],
            'sopir_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
