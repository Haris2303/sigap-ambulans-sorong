<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisFaskes;
use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Enums\TabPesanan;
use App\Exceptions\PenugasanTidakValidException;
use App\Exceptions\TransisiTidakValidException;
use App\Http\Controllers\Concerns\MembalasJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlasanOpsionalRequest;
use App\Http\Requests\Admin\AlasanWajibRequest;
use App\Http\Requests\Admin\TugaskanRequest;
use App\Models\Ambulans;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use App\Services\RingkasanDinkes;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    use MembalasJson;

    public function __construct(private readonly PesananWorkflow $alur) {}

    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', 'string'],
            'jenis_layanan' => ['nullable', 'string'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', 'string'],
        ]);

        $status = PesananStatus::tryFrom((string) ($filter['status'] ?? ''));
        $layanan = JenisLayanan::tryFrom((string) ($filter['jenis_layanan'] ?? ''));
        $kata = trim((string) ($filter['q'] ?? ''));
        $tab = TabPesanan::tryFrom((string) ($filter['tab'] ?? '')) ?? TabPesanan::Semua;
        $statusTab = array_map(fn (PesananStatus $s) => $s->value, array_filter(PesananStatus::cases(), fn (PesananStatus $s) => $tab->mencakup($s)));

        $query = Pesanan::with('pemohon')
            ->when($status, fn (Builder $q) => $q->where('status', $status->value))
            ->when($layanan, fn (Builder $q) => $q->where('jenis_layanan', $layanan->value))
            ->when($filter['dari'] ?? null, fn (Builder $q, string $d) => $q->where('created_at', '>=', \Illuminate\Support\Carbon::parse($d)->startOfDay()))
            ->when($filter['sampai'] ?? null, fn (Builder $q, string $d) => $q->where('created_at', '<=', \Illuminate\Support\Carbon::parse($d)->endOfDay()))
            ->when($tab !== TabPesanan::Semua, fn (Builder $q) => $q->whereIn('status', $statusTab))
            ->when($kata !== '', fn (Builder $q) => $q->cari($kata));

        // Pesanan yang masih berjalan lebih dulu: gawat dulu, lalu yang paling lama menunggu.
        $final = array_map(fn (PesananStatus $s) => $s->value, array_filter(PesananStatus::cases(), fn (PesananStatus $s) => $s->final()));
        $query->orderByRaw('CASE WHEN status IN ('.implode(',', array_fill(0, count($final), '?')).') THEN 1 ELSE 0 END', $final)
            ->prioritas();

        return view('admin.pesanan.index', [
            'daftar' => $query->paginate(config('sigap.per_halaman'))->withQueryString(),
            'filter' => $filter,
            'tab' => $tab,
            // Parameter yang dipertahankan saat berpindah tab (tab sendiri tidak).
            'queryTab' => collect($filter)->except('tab')->filter(fn ($v) => $v !== null && $v !== '')->all(),
            'adaFilter' => collect($filter)->except('tab')->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty(),
        ]);
    }

    public function ringkasan(RingkasanDinkes $ringkasan): JsonResponse
    {
        // Angka hidup dasbor: kartu statistik, petak ketersediaan, lencana menu, dan lonceng (kunci = data-ringkasan/data-lencana).
        return $this->ok($ringkasan->pesanan() + $ringkasan->ketersediaan());
    }

    public function show(Pesanan $pesanan): View
    {
        $pesanan->load(['pemohon', 'log.pelaku', 'ambulans.faskes', 'sopir', 'faskesTujuan', 'diverifikasiOleh']);
        $status = $pesanan->status;

        return view('admin.pesanan.show', [
            'pesanan' => $pesanan,
            'bolehVerifikasi' => $status === PesananStatus::Menunggu,
            'bolehTugaskan' => $status === PesananStatus::Terverifikasi,
            'bolehLepas' => $status === PesananStatus::Ditugaskan,
            'bolehBatalkan' => $status->bolehKe(PesananStatus::Dibatalkan) && $status !== PesananStatus::Menunggu,
            'opsiPenugasan' => $status === PesananStatus::Terverifikasi ? $this->opsiPenugasan($pesanan) : null,
        ]);
    }

    public function verifikasi(Pesanan $pesanan, Request $request): RedirectResponse
    {
        return $this->jalankan($pesanan, 'Pesanan berhasil diverifikasi.', fn () => $this->alur->verifikasi($pesanan, $request->user()));
    }

    public function tolak(AlasanWajibRequest $request, Pesanan $pesanan): RedirectResponse
    {
        return $this->jalankan($pesanan, 'Pesanan berhasil ditolak.', fn () => $this->alur->tolak($pesanan, $request->user(), $request->validated('alasan')));
    }

    public function tugaskan(TugaskanRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $ambulans = Ambulans::findOrFail($request->validated('ambulans_id'));
        $sopir = User::findOrFail($request->validated('sopir_id'));

        return $this->jalankan($pesanan, 'Ambulans dan sopir berhasil ditugaskan.', fn () => $this->alur->tugaskan($pesanan, $request->user(), $ambulans, $sopir));
    }

    public function lepas(AlasanOpsionalRequest $request, Pesanan $pesanan): RedirectResponse
    {
        return $this->jalankan($pesanan, 'Penugasan berhasil dilepas.', fn () => $this->alur->lepaskanPenugasan($pesanan, $request->user(), $request->validated('alasan')));
    }

    public function batalkan(AlasanWajibRequest $request, Pesanan $pesanan): RedirectResponse
    {
        return $this->jalankan($pesanan, 'Pesanan berhasil dibatalkan.', fn () => $this->alur->batalkanOlehAdmin($pesanan, $request->user(), $request->validated('alasan')));
    }

    private function jalankan(Pesanan $pesanan, string $pesanSukses, \Closure $aksi): RedirectResponse
    {
        try {
            $aksi();
        } catch (TransisiTidakValidException|PenugasanTidakValidException $e) {
            return redirect()->route('admin.pesanan.show', $pesanan)->with('galat', $e->getMessage());
        }

        return redirect()->route('admin.pesanan.show', $pesanan)->with('sukses', $pesanSukses);
    }

    /**
     * Pilihan penugasan: ambulans tersedia dikelompokkan per Faskes (yang sesuai saran lebih dulu,
     * hanya urutan) dan sopir online yang bebas, dikelompokkan per Faskes.
     *
     * @return array{ambulans: array<string, array<int, string>>, sopir: array<string, array<int, string>>}
     */
    private function opsiPenugasan(Pesanan $pesanan): array
    {
        $saran = $pesanan->jenis_layanan->jenisAmbulansSaran();
        $persalinan = $pesanan->jenis_layanan === JenisLayanan::Persalinan;

        $ambulans = Ambulans::with('faskes')
            ->where('status', StatusAmbulans::Tersedia->value)
            ->whereHas('faskes', fn (Builder $q) => $q->where('aktif', true))
            ->get()
            ->sortBy(fn (Ambulans $a) => [
                $a->jenis === $saran ? 0 : 1,
                $persalinan && $a->faskes->jenis !== JenisFaskes::Puskesmas ? 1 : 0,
                $a->faskes->nama,
                $a->plat_nomor,
            ]);

        $kelompokAmbulans = [];
        foreach ($ambulans as $a) {
            $label = $a->plat_nomor.', '.$a->jenis->label().($a->jenis === $saran ? ' (disarankan)' : '');
            $kelompokAmbulans[$a->faskes->nama][$a->id] = $label;
        }

        $sibuk = Pesanan::query()
            ->whereIn('status', array_map(fn (PesananStatus $s) => $s->value, array_filter(PesananStatus::cases(), fn (PesananStatus $s) => $s->memegangSumberDaya())))
            ->whereNotNull('sopir_id')->pluck('sopir_id');

        $kelompokSopir = [];
        $sopirOnline = User::with('faskes')->where('role', Role::Sopir->value)->where('aktif', true)
            ->where('sedang_online', true)->whereNotIn('id', $sibuk)->whereNotNull('faskes_id')
            ->orderBy('name')->get();
        foreach ($sopirOnline as $s) {
            $kelompokSopir[$s->faskes->nama][$s->id] = $s->name;
        }
        ksort($kelompokSopir);

        return ['ambulans' => $kelompokAmbulans, 'sopir' => $kelompokSopir];
    }
}
