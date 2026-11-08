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
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class PesananController extends Controller
{
    use MembalasJson;

    private const KUNCI_DRAF = 'draf_pesanan';

    /** Detik: pesanan aktif identik yang dibuat dalam jendela ini dianggap kiriman ganda dari satu draf (ketukan ganda, permintaan bersamaan). */
    private const JENDELA_GANDA_DETIK = 120;

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
        $pengguna = $request->user();
        $draf = $request->session()->get(self::KUNCI_DRAF);

        if (! is_array($draf)) {
            // Draf sudah terpakai: ketukan kedua sesudah pesanan terkirim menuju pesanan itu, bukan galat yang menyesatkan.
            $terkirim = $this->pesananTerkirim($pengguna);

            return $terkirim !== null
                ? redirect()->route('warga.pesanan.show', $terkirim)
                : redirect()->route('warga.pesanan.buat')->with('galat', 'Isi data pesanan terlebih dahulu.');
        }

        // Permintaan bersamaan dari satu pengguna diproses berurutan; yang kedua mendapati pesanan pertama dan memakainya.
        $kunci = Cache::lock('pesanan-baru:'.$pengguna->id, 10);
        try {
            $kunci->block(5);
        } catch (LockTimeoutException) {
            return redirect()->route('warga.pesanan.konfirmasi')->with('galat', 'Pesanan sedang diproses. Tunggu sebentar, lalu periksa Riwayat pesanan.');
        }

        try {
            $pesanan = $this->pesananTerkirim($pengguna, $draf) ?? $this->alur->buat($pengguna, $draf);
        } catch (TransisiTidakValidException $e) {
            return redirect()->route('warga.pesanan.buat')->with('galat', $e->getMessage());
        } finally {
            $kunci->release();
        }

        $request->session()->forget(self::KUNCI_DRAF);

        return redirect()->route('warga.pesanan.show', $pesanan)->with('sukses', 'Pesanan berhasil dikirim. Dinkes akan memverifikasi pesanan Anda.');
    }

    /** Pesanan aktif terbaru milik pengguna dalam jendela singkat; dengan $draf, hanya yang isinya sama. */
    private function pesananTerkirim(User $pengguna, ?array $draf = null): ?Pesanan
    {
        return Pesanan::where('user_id', $pengguna->id)
            ->aktif()
            ->where('created_at', '>=', now()->subSeconds(self::JENDELA_GANDA_DETIK))
            ->when($draf !== null, fn (Builder $q) => $q->where([
                'nama_pasien' => $draf['nama_pasien'],
                'no_telepon_kontak' => $draf['no_telepon_kontak'],
                'jenis_layanan' => $draf['jenis_layanan'],
                'lokasi_jemput' => $draf['lokasi_jemput'],
            ]))
            ->latest()->orderByDesc('id')->first();
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
