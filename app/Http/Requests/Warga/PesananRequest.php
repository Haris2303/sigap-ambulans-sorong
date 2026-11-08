<?php

namespace App\Http\Requests\Warga;

use App\Enums\JenisLayanan;
use App\Enums\TingkatDarurat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'jenis_layanan' => ['required', Rule::enum(JenisLayanan::class)],
            'tingkat_darurat' => ['required', Rule::enum(TingkatDarurat::class)],
            'lokasi_jemput' => ['required', 'string', 'max:255'],
            'lat_jemput' => ['nullable', 'required_with:lng_jemput', 'numeric', 'between:-90,90'],
            'lng_jemput' => ['nullable', 'required_with:lat_jemput', 'numeric', 'between:-180,180'],
            'faskes_tujuan_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('jenis_layanan') === JenisLayanan::Rujukan->value),
                Rule::exists('faskes', 'id')->where('aktif', true),
            ],
            'nama_pasien' => ['required', 'string', 'max:255'],
            'no_telepon_kontak' => ['required', 'string', 'regex:/^[0-9+][0-9]{7,18}$/'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'faskes_tujuan_id.required' => 'Faskes tujuan wajib dipilih untuk pesanan rujukan.',
            'no_telepon_kontak.regex' => 'Nomor kontak hanya boleh berisi angka (8 sampai 19 digit).',
            'lat_jemput.required_with' => 'Titik lokasi pada peta tidak lengkap. Pilih ulang lokasi.',
            'lng_jemput.required_with' => 'Titik lokasi pada peta tidak lengkap. Pilih ulang lokasi.',
        ];
    }
}
