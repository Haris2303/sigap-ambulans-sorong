<?php

namespace App\Services;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Rekap pesanan untuk laporan Dinkes pada satu periode (berdasarkan waktu pesanan dibuat).
 * Waktu respons = pesanan dibuat sampai ditugaskan; durasi layanan = ditugaskan sampai selesai.
 */
class LaporanPesanan
{
    public function __construct(
        public readonly Carbon $dari,
        public readonly Carbon $sampai,
    ) {}

    public static function periodeAwal(): self
    {
        return new self(now()->subDays(29)->startOfDay(), now()->endOfDay());
    }

    public static function dari(?string $dari, ?string $sampai): self
    {
        $awal = $dari ? Carbon::parse($dari)->startOfDay() : now()->subDays(29)->startOfDay();
        $akhir = $sampai ? Carbon::parse($sampai)->endOfDay() : now()->endOfDay();

        return new self($awal, $akhir);
    }

    private function query(): Builder
    {
        return Pesanan::query()->whereBetween('created_at', [$this->dari, $this->sampai]);
    }

    /** @return Collection<int, Pesanan> */
    public function baris(): Collection
    {
        return $this->query()
            ->with(['pemohon', 'faskesTujuan', 'ambulans.faskes', 'sopir'])
            ->orderBy('created_at')->orderBy('id')
            ->get();
    }

    /** @return array<string, mixed> */
    public function ringkas(): array
    {
        $semua = $this->query()->with('ambulans.faskes')->get();

        $perStatus = [];
        foreach (PesananStatus::cases() as $status) {
            $perStatus[$status->label()] = $semua->where('status', $status)->count();
        }

        $perJenis = [];
        foreach (JenisLayanan::cases() as $jenis) {
            $perJenis[$jenis->label()] = $semua->where('jenis_layanan', $jenis)->count();
        }

        $perFaskes = $semua->filter(fn (Pesanan $p) => $p->ambulans !== null)
            ->groupBy(fn (Pesanan $p) => $p->ambulans->faskes->nama)
            ->map->count()
            ->sortDesc()
            ->all();

        return [
            'dari' => $this->dari,
            'sampai' => $this->sampai,
            'total' => $semua->count(),
            'per_status' => $perStatus,
            'per_jenis' => $perJenis,
            'per_faskes' => $perFaskes,
            'respons' => $this->statistik($semua, fn (Pesanan $p) => self::responsDetik($p)),
            'durasi' => $this->statistik($semua, fn (Pesanan $p) => self::durasiDetik($p)),
        ];
    }

    /** Detik dari pesanan dibuat sampai ditugaskan; null bila belum ditugaskan. */
    public static function responsDetik(Pesanan $p): ?int
    {
        return $p->ditugaskan_pada === null ? null : (int) round($p->created_at->diffInSeconds($p->ditugaskan_pada, true));
    }

    /** Detik dari ditugaskan sampai selesai; null bila belum selesai. */
    public static function durasiDetik(Pesanan $p): ?int
    {
        return ($p->ditugaskan_pada === null || $p->selesai_pada === null)
            ? null
            : (int) round($p->ditugaskan_pada->diffInSeconds($p->selesai_pada, true));
    }

    /**
     * @param  \Closure(Pesanan): (int|float|null)  $selisih
     * @return array{jumlah: int, rata: ?int, tercepat: ?int, terlama: ?int} satuan detik
     */
    private function statistik($pesanan, \Closure $selisih): array
    {
        $nilai = $pesanan->map($selisih)->filter(fn ($n) => $n !== null)->map(fn ($n) => (int) round($n))->values();

        return [
            'jumlah' => $nilai->count(),
            'rata' => $nilai->isEmpty() ? null : (int) round($nilai->avg()),
            'tercepat' => $nilai->isEmpty() ? null : $nilai->min(),
            'terlama' => $nilai->isEmpty() ? null : $nilai->max(),
        ];
    }

    /** Menit bulat (pembulatan sama dengan formatDurasi), 0 bila tidak ada data. Dipakai grafik batang Laporan. */
    public static function menit(?int $detik): int
    {
        return $detik === null ? 0 : intdiv($detik + 30, 60);
    }

    /** "1 jam 5 menit", "7 menit", atau "-" bila tidak ada data. */
    public static function formatDurasi(?int $detik): string
    {
        if ($detik === null) {
            return '-';
        }
        $menit = intdiv($detik + 30, 60);
        if ($menit < 1) {
            return 'kurang dari 1 menit';
        }

        return $menit >= 60 ? intdiv($menit, 60).' jam '.($menit % 60).' menit' : $menit.' menit';
    }
}
