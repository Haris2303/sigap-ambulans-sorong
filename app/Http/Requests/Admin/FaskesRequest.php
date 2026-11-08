<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisFaskes;
use App\Enums\Kepemilikan;
use App\Models\Faskes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaskesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $faskes = $this->route('faskes');

        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique('faskes', 'nama')->ignore($faskes instanceof Faskes ? $faskes->id : null)],
            'jenis' => ['required', Rule::enum(JenisFaskes::class)],
            'kepemilikan' => ['nullable', Rule::enum(Kepemilikan::class)],
            'alamat' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:-180,180'],
            'aktif' => ['boolean'],
        ];
    }
}
