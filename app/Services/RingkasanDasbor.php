<?php

namespace App\Services;

use App\Enums\PeriodeDasbor;
use App\Enums\PesananStatus;
use App\Enums\TabPesanan;
use App\Models\Pesanan;
use App\Models\PesananLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Data dasbor Dinkes arah B (docs/RETEMA_B.md bagian 8.1) dalam satu array tegas; Blade hanya mencetaknya.
 * Query: statistik 1, ketersediaan 2, selesai+sebelumnya 1, sparkline 1, masuk+donat 1, seri selesai 1, pesanan
 * hari ini 1 = 8 (batas dokumen: paling banyak 14).
 */
class RingkasanDasbor
{
    /** Baris yang tampil pada tabel "Pesanan terbaru". */
    public const DASBOR_BARIS_TABEL = 5;

    /** Jumlah hari pada sparkline kartu statistik, termasuk hari ini. */
    public const DASBOR_HARI_SPARKLINE = 7;

    /** Hari singkat menurut Carbon::dayOfWeek (Minggu = 0); tidak bergantung pada bahasa aplikasi. */
    private const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    /** Status pertama yang menandai pesanan "mulai berjalan" (memegang sumber daya). */
    private const MULAI_BERJALAN = PesananStatus::Ditugaskan;

    public function __construct(private readonly RingkasanDinkes $ringkasan) {}

    public function ambil(TabPesanan $tab = TabPesanan::Semua, PeriodeDasbor $periode = PeriodeDasbor::Tujuh): array
    {
        $pesanan = $this->ringkasan->pesanan();
        $ketersediaan = $this->ringkasan->ketersediaan();
        [$selesai, $selesaiSebelumnya] = $this->selesaiPeriode($periode);
        $sparkline = $this->sparkline();
        [$masuk, $donat] = $this->masukDanDonat($periode);
        $seriSelesai = $this->seriSelesai($periode);

        return [
            'periode' => $periode,
            'diperbarui' => now()->format('H.i'),
            'statistik' => [
                'pesanan_menunggu' => $pesanan['pesanan_menunggu'],
                'pesanan_menunggu_gawat' => $pesanan['pesanan_menunggu_gawat'],
                'pesanan_menunggu_tertua_menit' => $pesanan['pesanan_menunggu_tertua_menit'],
                'pesanan_terverifikasi' => $pesanan['pesanan_terverifikasi'],
                'pesanan_berjalan' => $pesanan['pesanan_berjalan'],
                'pesanan_selesai' => $selesai,
                'selesai_delta' => $selesai - $selesaiSebelumnya,
            ],
            'sparkline' => $sparkline,
            'ketersediaan' => [
                'ambulans' => ['tersedia' => $ketersediaan['ambulans_tersedia'], 'total' => $ketersediaan['ambulans_total']],
                'bed_igd' => ['tersedia' => $ketersediaan['bed_igd_tersedia'], 'total' => $ketersediaan['bed_igd_total']],
                'bed_rawat_inap' => ['tersedia' => $ketersediaan['bed_rawat_inap_tersedia'], 'total' => $ketersediaan['bed_rawat_inap_total']],
                'dokter' => ['tersedia' => $ketersediaan['dokter_tersedia'], 'total' => $ketersediaan['dokter_total']],
            ],
            'grafik' => $this->grafik($periode, $masuk, $seriSelesai),
            'donat' => $donat,
            'terbaru' => $this->terbaru($tab),
        ];
    }

    /** @return array{0: int, 1: int} [selesai periode ini, selesai periode sebanding sebelumnya] */
    private function selesaiPeriode(PeriodeDasbor $periode): array
    {
        [$awal, $akhir] = $this->rentang($periode);
        $n = $periode->jumlahHari();
        $awalSebelumnya = $awal->copy()->subDays($n);
        $akhirSebelumnya = $akhir->copy()->subDays($n);

        $baris = Pesanan::query()->where('status', PesananStatus::Selesai)
            ->selectRaw(
                'sum(case when selesai_pada >= ? and selesai_pada <= ? then 1 else 0 end) as sekarang,'
                .' sum(case when selesai_pada >= ? and selesai_pada <= ? then 1 else 0 end) as sebelumnya',
                [$awal, $akhir, $awalSebelumnya, $akhirSebelumnya],
            )->toBase()->first();

        return [(int) ($baris->sekarang ?? 0), (int) ($baris->sebelumnya ?? 0)];
    }

    /** @return array{0: Carbon, 1: Carbon} [awal, akhir] jendela periode, awal selalu awal hari (00:00). */
    private function rentang(PeriodeDasbor $periode): array
    {
        $akhir = now();
        $awal = $periode === PeriodeDasbor::Hari ? $akhir->copy()->startOfDay() : $akhir->copy()->subDays($periode->jumlahHari() - 1)->startOfDay();

        return [$awal, $akhir];
    }

    /**
     * Seri harian 7 hari terakhir (dekorasi, tidak ikut filter periode) dari kejadian status di pesanan_log:
     * terverifikasi, mulai berjalan (ditugaskan), dan selesai.
     *
     * @return array{pesanan_terverifikasi: array<int, int>, pesanan_berjalan: array<int, int>, pesanan_selesai: array<int, int>}
     */
    private function sparkline(): array
    {
        $awal = now()->subDays(self::DASBOR_HARI_SPARKLINE - 1)->startOfDay();
        $kunci = [
            'pesanan_terverifikasi' => PesananStatus::Terverifikasi,
            'pesanan_berjalan' => self::MULAI_BERJALAN,
            'pesanan_selesai' => PesananStatus::Selesai,
        ];

        $baris = PesananLog::query()
            ->whereIn('ke_status', array_map(fn (PesananStatus $s) => $s->value, $kunci))
            ->where('created_at', '>=', $awal)
            ->get(['ke_status', 'created_at']);

        $hitung = $baris->countBy(fn (PesananLog $l) => $l->ke_status->value.'|'.$l->created_at->toDateString());

        $hasil = [];
        foreach ($kunci as $nama => $status) {
            $seri = [];
            for ($i = 0; $i < self::DASBOR_HARI_SPARKLINE; $i++) {
                $hari = $awal->copy()->addDays($i);
                $seri[] = (int) ($hitung[$status->value.'|'.$hari->toDateString()] ?? 0);
            }
            $hasil[$nama] = $seri;
        }

        return $hasil;
    }

    /**
     * Satu query: pesanan dibuat dalam periode (status dan waktu dibuat). Dipakai untuk seri "masuk" (dikelompokkan
     * per hari atau per jam) dan donat status (dikelompokkan per status).
     *
     * @return array{0: array<string, int>, 1: array{segments: array<int, array{label: string, value: int, color: string}>, total: int}}
     */
    private function masukDanDonat(PeriodeDasbor $periode): array
    {
        [$awal, $akhir] = $this->rentang($periode);
        $baris = Pesanan::query()->whereBetween('created_at', [$awal, $akhir])->get(['status', 'created_at']);

        $masuk = $baris->countBy(fn (Pesanan $p) => $this->bucket($p->created_at, $periode));

        $perStatus = $baris->countBy(fn (Pesanan $p) => $p->status->value);
        $urutanEnum = array_flip(array_map(fn (PesananStatus $s) => $s->value, PesananStatus::cases()));
        $segmen = array_values(array_filter(array_map(fn (PesananStatus $s) => [
            'label' => $s->label(),
            'value' => (int) ($perStatus[$s->value] ?? 0),
            'color' => $s->warnaChart(),
            'urutan' => $urutanEnum[$s->value],
        ], PesananStatus::cases()), fn (array $s) => $s['value'] > 0));

        // Nilai terbesar dulu; seri sama mengikuti urutan Enum (docs/RETEMA_B.md 7.1).
        usort($segmen, fn (array $a, array $b) => [$b['value'], $a['urutan']] <=> [$a['value'], $b['urutan']]);
        $segmen = array_map(fn (array $s) => array_diff_key($s, ['urutan' => null]), $segmen);

        return [$masuk->all(), ['segments' => $segmen, 'total' => $baris->count()]];
    }

    /** Seri pesanan selesai per bucket dalam periode, dari selesai_pada (bukan created_at): satu query. */
    private function seriSelesai(PeriodeDasbor $periode): array
    {
        [$awal, $akhir] = $this->rentang($periode);
        $waktu = Pesanan::query()->where('status', PesananStatus::Selesai)
            ->whereBetween('selesai_pada', [$awal, $akhir])->pluck('selesai_pada');

        return $waktu->countBy(fn (Carbon $t) => $this->bucket($t, $periode))->all();
    }

    private function bucket(Carbon $waktu, PeriodeDasbor $periode): string
    {
        return $periode->perJam() ? $waktu->format('H') : $waktu->toDateString();
    }

    /** @return array{labels: array<int, string>, tick_step: int, tooltip_titles: array<int, string>, masuk: array<int, int>, pesanan_selesai: array<int, int>} */
    private function grafik(PeriodeDasbor $periode, array $masuk, array $seriSelesai): array
    {
        [$awal, $akhir] = $this->rentang($periode);

        if ($periode->perJam()) {
            $labels = [];
            $tooltip = [];
            $nilaiMasuk = [];
            $nilaiSelesai = [];
            for ($jam = 0; $jam < 24; $jam++) {
                $kunci = sprintf('%02d', $jam);
                $labels[] = $kunci;
                $tooltip[] = $kunci.'.00';
                $nilaiMasuk[] = (int) ($masuk[$kunci] ?? 0);
                $nilaiSelesai[] = (int) ($seriSelesai[$kunci] ?? 0);
            }

            return ['labels' => $labels, 'tick_step' => 3, 'tooltip_titles' => $tooltip, 'masuk' => $nilaiMasuk, 'pesanan_selesai' => $nilaiSelesai];
        }

        $jumlahHari = (int) $awal->diffInDays($akhir) + 1;
        $labels = [];
        $tooltip = [];
        $nilaiMasuk = [];
        $nilaiSelesai = [];
        for ($i = 0; $i < $jumlahHari; $i++) {
            $hari = $awal->copy()->addDays($i);
            $kunci = $hari->toDateString();
            $labels[] = $periode === PeriodeDasbor::Tujuh ? self::HARI[$hari->dayOfWeek] : $hari->format('d/m');
            $tooltip[] = self::HARI[$hari->dayOfWeek].' '.$hari->format('d/m');
            $nilaiMasuk[] = (int) ($masuk[$kunci] ?? 0);
            $nilaiSelesai[] = (int) ($seriSelesai[$kunci] ?? 0);
        }

        return ['labels' => $labels, 'tick_step' => $periode === PeriodeDasbor::TigaPuluh ? 5 : 1, 'tooltip_titles' => $tooltip, 'masuk' => $nilaiMasuk, 'pesanan_selesai' => $nilaiSelesai];
    }

    /**
     * Satu query: pesanan dibuat hari ini (jendela "Pesanan terbaru"), disaring per tab dan dibatasi 5 baris untuk
     * tampilan. jumlah_hari_ini tidak ikut saringan tab.
     *
     * @return array{tab: TabPesanan, baris: Collection<int, Pesanan>, jumlah_hari_ini: int}
     */
    private function terbaru(TabPesanan $tab): array
    {
        $jendela = Pesanan::query()
            ->select(['id', 'kode', 'nama_pasien', 'jenis_layanan', 'tingkat_darurat', 'status', 'created_at'])
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')->get();

        $baris = $jendela->filter(fn (Pesanan $p) => $tab->mencakup($p->status))->take(self::DASBOR_BARIS_TABEL)->values();

        return ['tab' => $tab, 'baris' => $baris, 'jumlah_hari_ini' => $jendela->count()];
    }
}
