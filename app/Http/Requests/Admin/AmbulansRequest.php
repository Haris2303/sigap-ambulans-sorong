<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisAmbulans;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AmbulansRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $ambulans = $this->route('ambulans');

        return [
            'faskes_id' => ['required', 'integer', Rule::exists('faskes', 'id')->where('aktif', true)],
            'sopir_id' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('role', Role::Sopir->value)->where('faskes_id', $this->input('faskes_id')),
            ],
            'plat_nomor' => ['required', 'string', 'max:20', Rule::unique('ambulans', 'plat_nomor')->ignore($ambulans instanceof Ambulans ? $ambulans->id : null)],
            'jenis' => ['required', Rule::enum(JenisAmbulans::class)],
            'merek_model' => ['nullable', 'string', 'max:255'],
            // "bertugas" hanya diatur PesananWorkflow; Dinkes hanya memilih tersedia atau servis.
            'status' => ['sometimes', Rule::in(array_keys(StatusAmbulans::opsiManual()))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'sopir_id.exists' => 'Sopir tetap harus berperan Sopir dan berasal dari Faskes yang sama dengan ambulans.',
        ];
    }
}
