<?php

namespace App\Http\Requests\Petugas;

use App\Enums\StatusAmbulans;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatusAmbulansRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(StatusAmbulans::opsiManual()))],
        ];
    }
}
