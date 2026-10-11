<?php

namespace App\Services;

use App\Enums\PesananStatus;
use App\Enums\TabPesanan;
use App\Models\Pesanan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Data dasbor Dinkes (docs/DESAIN_DASBOR_MATERIAL.md bagian 6) dalam satu array tegas; Blade hanya mencetaknya.
 * Query: statistik 2, ketersediaan 2, batang 1, sepuluh pesanan terbaru 1 (donat dan tabel dihitung dari hasil itu) = 6.
 */
class RingkasanDasbor
{
    /** Jendela "pesanan terbaru": dasar donat status dan tabel pesanan terbaru. */
    public const DASBOR_JUMLAH_PESANAN_TERBARU = 10;

    /** Baris yang tampil pada tabel pesanan terbaru. */
    public const DASBOR_BARIS_TABEL = 5;

    /** Jumlah hari pada grafik batang, termasuk hari ini. */
    public const DASBOR_HARI_GRAFIK = 7;

    /** Hari singkat menurut Carbon::dayOfWeek (Minggu = 0); tidak bergantung pada bahasa aplikasi. */
    private const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function __construct(private readonly RingkasanDinkes $ringkasan) {}

    /**
     * @return array{
     *     statistik: array<string, int>,
     *     ketersediaan: array<string, int>,
     *     batang: array{labels: array<int, array{0: string, 1: string}>, values: array<int, int>, highlight: int, rentang: string},
     *     donat: array{segments: array<int, array{label: string, value: int, color: string}>, total: int},
     *     terbaru: array{tab: TabPesanan, baris: Collection<int, Pesanan>, jumlah_tampil: int, jumlah_jendela: int}
     * }
     */
    public function ambil(TabPesanan $tab = TabPesanan::Semua): array
    {
        $jendela = Pesanan::query()
            ->select(['id', 'kode', 'nama_pasien', 'jenis_layanan', 'tingkat_darurat', 'status', 'created_at'])
            ->latest()->orderByDesc('id')
            ->limit(self::DASBOR_JUMLAH_PESANAN_TERBARU)
            ->get();

        $baris = $jendela->filter(fn (Pesanan $p) => $tab->mencakup($p->status))->take(self::DASBOR_BARIS_TABEL)->values();

        return [
            'statistik' => $this->ringkasan->pesanan(),
            'ketersediaan' => $this->ringkasan->ketersediaan(),
            'batang' => $this->batang(),
            'donat' => [
                'segments' => array_map(
                    fn (PesananStatus $s) => ['label' => $s->label(), 'value' => $jendela->where('status', $s)->count(), 'color' => $s->warnaGrafik()],
                    PesananStatus::cases(),
                ),
                'total' => $jendela->count(),
            ],
            'terbaru' => [
                'tab' => $tab,
                'baris' => $baris,
                'jumlah_tampil' => $baris->count(),
                'jumlah_jendela' => $jendela->count(),
            ],
        ];
    }

    /** @return array{labels: array<int, array{0: string, 1: string}>, values: array<int, int>, highlight: int, rentang: string} */
    private function batang(): array
    {
        $hariIni = now()->startOfDay();
        $awal = $hariIni->copy()->subDays(self::DASBOR_HARI_GRAFIK - 1);

        $hitung = Pesanan::query()->where('created_at', '>=', $awal)->pluck('created_at')
            ->countBy(fn (Carbon $t) => $t->toDateString());

        $labels = [];
        $values = [];
        for ($i = 0; $i < self::DASBOR_HARI_GRAFIK; $i++) {
            $hari = $awal->copy()->addDays($i);
            $labels[] = [self::HARI[$hari->dayOfWeek], $hari->format('d/m')];
            $values[] = (int) ($hitung[$hari->toDateString()] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'highlight' => self::DASBOR_HARI_GRAFIK - 1,
            'rentang' => $this->rentang($awal, $hariIni),
        ];
    }

    /** "5 sampai 11 Okt 2026"; bulan atau tahun yang berbeda ditulis lengkap di sisi awal ("28 Sep sampai 4 Okt 2026"). */
    private function rentang(Carbon $awal, Carbon $akhir): string
    {
        $tanggal = fn (Carbon $t, bool $bulan, bool $tahun) => $t->day.($bulan ? ' '.self::BULAN[$t->month - 1] : '').($tahun ? ' '.$t->year : '');

        $tahunBeda = $awal->year !== $akhir->year;
        $bulanBeda = $tahunBeda || $awal->month !== $akhir->month;

        return $tanggal($awal, $bulanBeda, $tahunBeda).' sampai '.$tanggal($akhir, true, true);
    }
}
