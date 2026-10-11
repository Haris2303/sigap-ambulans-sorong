<?php

namespace App\Http\Requests\Admin;

use App\Services\LaporanPesanan;
use Illuminate\Foundation\Http\FormRequest;

class LaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ];
    }

    public function laporan(): LaporanPesanan
    {
        return LaporanPesanan::dari($this->validated('dari'), $this->validated('sampai'));
    }
}
