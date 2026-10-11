<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Validasi akun Sopir dan Petugas Faskes (peran ditentukan controller, bukan isian). */
class AkunFaskesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $akun = $this->route('sopir') ?? $this->route('petugas');
        $ubah = $akun instanceof User;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ubah ? $akun->id : null)],
            'no_telepon' => ['nullable', 'string', 'regex:/^[0-9+][0-9]{7,18}$/'],
            'faskes_id' => ['required', 'integer', 'exists:faskes,id'],
            'password' => [$ubah ? 'nullable' : 'required', 'string', Password::min(8)],
            'aktif' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'no_telepon.regex' => 'Nomor telepon hanya boleh berisi angka (8 sampai 19 digit).',
        ];
    }
}
