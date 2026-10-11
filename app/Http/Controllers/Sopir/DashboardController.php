<?php

namespace App\Http\Controllers\Sopir;

use App\Enums\PesananStatus;
use App\Exceptions\PenugasanTidakValidException;
use App\Exceptions\TransisiTidakValidException;
use App\Http\Controllers\Concerns\MembalasJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sopir\LokasiRequest;
use App\Http\Requests\Sopir\OnlineRequest;
use App\Http\Requests\Sopir\TolakPenugasanRequest;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use MembalasJson;

    public function __construct(private readonly PesananWorkflow $alur) {}

    public function dashboard(Request $request): View
    {
        $sopir = $request->user();
        $penugasan = $this->penugasanAktif($sopir);

        return view('sopir.dashboard', [
            'sopir' => $sopir,
            'penugasan' => $penugasan,
            'kunciPenugasan' => $penugasan ? $penugasan->id.':'.$penugasan->status->value : '',
            'riwayat' => Pesanan::where('sopir_id', $sopir->id)
                ->berstatus(PesananStatus::Selesai)->latest('selesai_pada')->limit(5)->get(),
        ]);
    }

    public function online(OnlineRequest $request): RedirectResponse|JsonResponse
    {
        $sopir = $request->user();
        $online = $request->boolean('online');

        if (! $online && $sopir->sedangBertugas()) {
            return $this->tanggapi($request, 'Selesaikan atau serahkan penugasan aktif sebelum offline.', gagal: true);
        }

        $sopir->update(['sedang_online' => $online]);

        return $this->tanggapi($request, $online ? 'Anda sekarang online dan dapat menerima penugasan.' : 'Anda sekarang offline.', ['online' => $online]);
    }

    public function lokasi(LokasiRequest $request): JsonResponse
    {
        $request->user()->update([
            'lat' => $request->validated('lat'),
            'lng' => $request->validated('lng'),
            'lokasi_diperbarui_pada' => now(),
        ]);

        return $this->ok(['diperbarui_pada' => now()->toIso8601String()]);
    }

    /** JSON untuk polling dasbor Sopir (penugasan aktif dan posisi terakhir sendiri). */
    public function penugasan(Request $request): JsonResponse
    {
        $sopir = $request->user();
        $penugasan = $this->penugasanAktif($sopir);

        return $this->ok([
            'penugasan' => $penugasan === null ? null : [
                'id' => $penugasan->id,
                'kode' => $penugasan->kode,
                'status' => $penugasan->status->value,
                'label' => $penugasan->status->label(),
                'jenis_layanan' => $penugasan->jenis_layanan->label(),
                'tingkat_darurat' => $penugasan->tingkat_darurat->label(),
                'nama_pasien' => $penugasan->nama_pasien,
                'lokasi_jemput' => $penugasan->lokasi_jemput,
                'lat' => $penugasan->lat_jemput === null ? null : (float) $penugasan->lat_jemput,
                'lng' => $penugasan->lng_jemput === null ? null : (float) $penugasan->lng_jemput,
                'faskes_tujuan' => $penugasan->faskesTujuan?->nama,
            ],
            'sopir' => [
                'lat' => $sopir->lat === null ? null : (float) $sopir->lat,
                'lng' => $sopir->lng === null ? null : (float) $sopir->lng,
                'online' => $sopir->sedang_online,
            ],
        ]);
    }

    public function terima(Request $request, Pesanan $pesanan): RedirectResponse|JsonResponse
    {
        return $this->jalankan($request, 'Penugasan diterima.', fn () => $this->alur->terima($pesanan, $request->user()));
    }

    public function tolak(TolakPenugasanRequest $request, Pesanan $pesanan): RedirectResponse|JsonResponse
    {
        return $this->jalankan($request, 'Penugasan ditolak. Dinkes akan menugaskan sopir lain.', fn () => $this->alur->tolakPenugasan($pesanan, $request->user(), $request->validated('alasan')));
    }

    public function berangkat(Request $request, Pesanan $pesanan): RedirectResponse|JsonResponse
    {
        return $this->jalankan($request, 'Perjalanan dimulai.', fn () => $this->alur->mulaiPerjalanan($pesanan, $request->user()));
    }

    public function selesaikan(Request $request, Pesanan $pesanan): RedirectResponse|JsonResponse
    {
        return $this->jalankan($request, 'Pesanan selesai.', fn () => $this->alur->selesaikan($pesanan, $request->user()));
    }

    private function penugasanAktif(User $sopir): ?Pesanan
    {
        return Pesanan::with(['ambulans.faskes', 'faskesTujuan', 'pemohon'])
            ->where('sopir_id', $sopir->id)
            ->berstatus(PesananStatus::Ditugaskan, PesananStatus::Diterima, PesananStatus::DalamPerjalanan)
            ->latest('ditugaskan_pada')
            ->first();
    }

    private function jalankan(Request $request, string $pesanSukses, \Closure $aksi): RedirectResponse|JsonResponse
    {
        try {
            $aksi();
        } catch (TransisiTidakValidException|PenugasanTidakValidException $e) {
            return $this->tanggapi($request, $e->getMessage(), gagal: true);
        }

        return $this->tanggapi($request, $pesanSukses);
    }

    /** JSON bila diminta JSON, selain itu kembali ke dasbor dengan pesan flash. */
    private function tanggapi(Request $request, string $pesan, array $data = [], bool $gagal = false): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return $gagal ? $this->gagal($pesan) : $this->ok($data + ['pesan' => $pesan]);
        }

        return redirect()->route('sopir.dashboard')->with($gagal ? 'galat' : 'sukses', $pesan);
    }
}
