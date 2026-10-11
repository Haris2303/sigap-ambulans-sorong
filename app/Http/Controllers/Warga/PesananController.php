<?php

namespace App\Http\Controllers\Warga;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\StatusAmbulans;
use App\Enums\TingkatDarurat;
use App\Exceptions\TransisiTidakValidException;
use App\Http\Controllers\Concerns\MembalasJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\BatalkanPesananRequest;
use App\Http\Requests\Warga\PesananRequest;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Services\PesananWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PesananController extends Controller
{
    use MembalasJson;

    private const KUNCI_DRAF = 'draf_pesanan';

    public function __construct(private readonly PesananWorkflow $alur) {}

    public function index(): View
    {
        return view('warga.pesanan.index', [
            'daftar' => Pesanan::where('user_id', auth()->id())
                ->latest()->orderByDesc('id')
                ->paginate(config('sigap.per_halaman')),
        ]);
    }

    public function buat(Request $request): View
    {
        $draf = $request->session()->get(self::KUNCI_DRAF, []);

        return view('warga.pesanan.buat', [
            'draf' => $draf,
            'faskes' => $this->faskesAktif(),
            'noTeleponAwal' => $draf['no_telepon_kontak'] ?? auth()->user()->no_telepon,
        ]);
    }

    /** Validasi, simpan draf di session (belum membuat pesanan), lalu ke halaman konfirmasi. */
    public function tinjau(PesananRequest $request): RedirectResponse
    {
        $request->session()->put(self::KUNCI_DRAF, $request->validated());

        return redirect()->route('warga.pesanan.konfirmasi');
    }

    public function konfirmasi(Request $request): View|RedirectResponse
    {
        $draf = $request->session()->get(self::KUNCI_DRAF);
        if (! is_array($draf)) {
            return redirect()->route('warga.pesanan.buat')->with('galat', 'Isi data pesanan terlebih dahulu.');
        }

        return view('warga.pesanan.konfirmasi', [
            'draf' => $draf,
            'jenisLayanan' => JenisLayanan::from($draf['jenis_layanan']),
            'tingkatDarurat' => TingkatDarurat::from($draf['tingkat_darurat']),
            'faskesTujuan' => empty($draf['faskes_tujuan_id']) ? null : Faskes::find($draf['faskes_tujuan_id']),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $draf = $request->session()->get(self::KUNCI_DRAF);
        if (! is_array($draf)) {
            return redirect()->route('warga.pesanan.buat')->with('galat', 'Isi data pesanan terlebih dahulu.');
        }

        try {
            $pesanan = $this->alur->buat($request->user(), $draf);
        } catch (TransisiTidakValidException $e) {
            return redirect()->route('warga.pesanan.buat')->with('galat', $e->getMessage());
        }

        $request->session()->forget(self::KUNCI_DRAF);

        return redirect()->route('warga.pesanan.show', $pesanan)->with('sukses', 'Pesanan berhasil dikirim. Dinkes akan memverifikasi pesanan Anda.');
    }

    public function show(Pesanan $pesanan): View
    {
        Gate::authorize('view', $pesanan);
        $pesanan->load(['log.pelaku', 'ambulans.faskes', 'sopir', 'faskesTujuan']);

        return view('warga.pesanan.show', [
            'pesanan' => $pesanan,
            'bolehBatal' => Gate::allows('batalkan', $pesanan),
            'tampilkanSopir' => $pesanan->sopir_id !== null && $pesanan->status->memegangSumberDaya(),
            'tampilkanPeta' => in_array($pesanan->status, [PesananStatus::Diterima, PesananStatus::DalamPerjalanan], true)
                && $pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null,
        ]);
    }

    /** JSON untuk polling halaman lacak. */
    public function status(Pesanan $pesanan): JsonResponse
    {
        Gate::authorize('view', $pesanan);
        $pesanan->load(['ambulans', 'sopir']);

        $sopir = null;
        if ($pesanan->sopir_id !== null && $pesanan->status->memegangSumberDaya()) {
            $berbagiLokasi = in_array($pesanan->status, [PesananStatus::Diterima, PesananStatus::DalamPerjalanan], true)
                && $pesanan->sopir->lat !== null && $pesanan->sopir->lng !== null;
            $sopir = [
                'nama' => $pesanan->sopir->name,
                'telepon' => $pesanan->sopir->no_telepon,
                'lat' => $berbagiLokasi ? (float) $pesanan->sopir->lat : null,
                'lng' => $berbagiLokasi ? (float) $pesanan->sopir->lng : null,
                'diperbarui_pada' => $pesanan->sopir->lokasi_diperbarui_pada?->toIso8601String(),
            ];
        }

        return $this->ok([
            'kode' => $pesanan->kode,
            'status' => $pesanan->status->value,
            'label' => $pesanan->status->label(),
            'nada' => $pesanan->status->nada(),
            'final' => $pesanan->status->final(),
            'ambulans' => $pesanan->ambulans === null ? null : [
                'plat_nomor' => $pesanan->ambulans->plat_nomor,
                'jenis' => $pesanan->ambulans->jenis->label(),
            ],
            'sopir' => $sopir,
        ]);
    }

    public function batalkan(BatalkanPesananRequest $request, Pesanan $pesanan): RedirectResponse
    {
        Gate::authorize('batalkan', $pesanan);

        try {
            $this->alur->batalkanOlehWarga($pesanan, $request->user(), $request->validated('alasan'));
        } catch (TransisiTidakValidException $e) {
            return redirect()->route('warga.pesanan.show', $pesanan)->with('galat', $e->getMessage());
        }

        return redirect()->route('warga.pesanan.show', $pesanan)->with('sukses', 'Pesanan berhasil dibatalkan.');
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Faskes> */
    private function faskesAktif()
    {
        return Faskes::aktif()
            ->withCount(['ambulans as ambulans_tersedia_count' => fn (Builder $q) => $q->where('status', StatusAmbulans::Tersedia->value)])
            ->orderBy('nama')
            ->get();
    }
}
