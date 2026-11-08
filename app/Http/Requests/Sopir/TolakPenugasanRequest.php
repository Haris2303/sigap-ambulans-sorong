<?php

namespace App\Http\Requests\Sopir;

use Illuminate\Foundation\Http\FormRequest;

class TolakPenugasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'max:500'],
        ];
    }
}
