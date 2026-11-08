<?php

namespace App\Http\Controllers\Publik;

use App\Enums\JenisFaskes;
use App\Enums\StatusAmbulans;
use App\Http\Controllers\Concerns\MembalasJson;
use App\Http\Controllers\Controller;
use App\Models\Faskes;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaskesController extends Controller
{
    use MembalasJson;

    public function index(Request $request): View
    {
        $jenis = JenisFaskes::tryFrom((string) $request->query('jenis'));

        return view('publik.faskes', [
            'faskes' => $this->daftar($jenis),
            'jenis' => $jenis,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $jenis = JenisFaskes::tryFrom((string) $request->query('jenis'));

        return $this->ok([
            'faskes' => $this->daftar($jenis)->map(fn (Faskes $f) => [
                'id' => $f->id,
                'nama' => $f->nama,
                'jenis' => $f->jenis->value,
                'jenis_label' => $f->jenis->label(),
                'alamat' => $f->alamat,
                'telepon' => $f->telepon,
                'lat' => $f->lat === null ? null : (float) $f->lat,
                'lng' => $f->lng === null ? null : (float) $f->lng,
                'bed_igd_tersedia' => $f->bed_igd_tersedia,
                'bed_igd_total' => $f->bed_igd_total,
                'bed_rawat_inap_tersedia' => $f->bed_rawat_inap_tersedia,
                'bed_rawat_inap_total' => $f->bed_rawat_inap_total,
                'dokter_umum_tersedia' => $f->dokter_umum_tersedia,
                'dokter_spesialis_tersedia' => $f->dokter_spesialis_tersedia,
                'ambulans_tersedia' => (int) $f->ambulans_tersedia_count,
                'diperbarui_pada' => $f->ketersediaan_diperbarui_pada?->toIso8601String(),
                'belum_diperbarui' => $f->belumDiperbarui(),
                'data_lama' => $f->dataLama(),
            ])->values(),
        ]);
    }

    /** @return Collection<int, Faskes> */
    private function daftar(?JenisFaskes $jenis): Collection
    {
        return Faskes::aktif()
            ->when($jenis, fn (Builder $q) => $q->where('jenis', $jenis->value))
            ->withCount(['ambulans as ambulans_tersedia_count' => fn (Builder $q) => $q->where('status', StatusAmbulans::Tersedia->value)])
            ->orderBy('nama')
            ->get();
    }
}
