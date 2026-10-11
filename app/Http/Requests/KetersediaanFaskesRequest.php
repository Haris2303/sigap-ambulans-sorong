<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Aturan ketersediaan Faskes, dipakai Dinkes dan Petugas Faskes: tersedia tidak boleh melebihi total. */
class KetersediaanFaskesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'bed_igd_total' => ['required', 'integer', 'min:0', 'max:100000'],
            'bed_igd_tersedia' => ['required', 'integer', 'min:0', 'lte:bed_igd_total'],
            'bed_rawat_inap_total' => ['required', 'integer', 'min:0', 'max:100000'],
            'bed_rawat_inap_tersedia' => ['required', 'integer', 'min:0', 'lte:bed_rawat_inap_total'],
            'dokter_umum_tersedia' => ['required', 'integer', 'min:0', 'max:100000'],
            'dokter_spesialis_tersedia' => ['required', 'integer', 'min:0', 'max:100000'],
            'dokter_spesialis_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'bed_igd_tersedia.lte' => 'Bed IGD tersedia tidak boleh lebih dari total bed IGD.',
            'bed_rawat_inap_tersedia.lte' => 'Bed rawat inap tersedia tidak boleh lebih dari total bed rawat inap.',
        ];
    }
}
