<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LaporanRequest;
use App\Models\Pesanan;
use App\Services\LaporanPesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(LaporanRequest $request): View
    {
        $laporan = $request->laporan();
        $data = $laporan->ringkas();

        return view('admin.laporan.index', [
            'data' => $data,
            'dari' => $laporan->dari->toDateString(),
            'sampai' => $laporan->sampai->toDateString(),
            'segmenStatus' => $this->segmen($data['per_status'], PesananStatus::cases()),
            'segmenJenis' => $this->segmen($data['per_jenis'], JenisLayanan::cases()),
            'grafikFaskes' => [
                'labels' => array_keys($data['per_faskes']),
                'data' => array_values($data['per_faskes']),
            ],
            'grafikRespons' => $this->grafikWaktu($data['respons']),
            'grafikDurasi' => $this->grafikWaktu($data['durasi']),
        ]);
    }

    /**
     * Segmen donat (label, value, color) dari kasus Enum berurut dan peta label=>jumlah. PesananStatus dan
     * JenisLayanan sama-sama punya label() dan warnaChart(), dipakai bergantian untuk kedua donat Laporan.
     *
     * @param  array<string, int>  $perLabel
     * @param  array<int, PesananStatus|JenisLayanan>  $kasus
     * @return array<int, array{label: string, value: int, color: string}>
     */
    private function segmen(array $perLabel, array $kasus): array
    {
        return array_map(fn ($k) => [
            'label' => $k->label(),
            'value' => $perLabel[$k->label()] ?? 0,
            'color' => $k->warnaChart(),
        ], $kasus);
    }

    /**
     * Grafik batang 3 kategori (Tercepat, Rata-rata, Terlama) dari statistik respons/durasi (detik): nilai dalam
     * menit untuk tinggi batang, dan label berformat ("1 jam 0 menit") untuk tabel sr-only dan tooltip.
     *
     * @param  array{jumlah: int, rata: ?int, tercepat: ?int, terlama: ?int}  $statistik
     * @return array{labels: array<int, string>, data: array<int, int>, valueLabels: array<int, string>}
     */
    private function grafikWaktu(array $statistik): array
    {
        $urutan = ['tercepat' => 'Tercepat', 'rata' => 'Rata-rata', 'terlama' => 'Terlama'];

        return [
            'labels' => array_values($urutan),
            'data' => array_map(fn ($k) => LaporanPesanan::menit($statistik[$k]), array_keys($urutan)),
            'valueLabels' => array_map(fn ($k) => LaporanPesanan::formatDurasi($statistik[$k]), array_keys($urutan)),
        ];
    }

    public function pdf(LaporanRequest $request): Response
    {
        $laporan = $request->laporan();

        return Pdf::loadView('pdf.laporan', ['data' => $laporan->ringkas(), 'dibuat' => now()])
            ->setPaper('a4')
            ->download('laporan-sigap-'.$laporan->dari->format('Ymd').'-'.$laporan->sampai->format('Ymd').'.pdf');
    }

    public function csv(LaporanRequest $request): StreamedResponse
    {
        $laporan = $request->laporan();
        $nama = 'laporan-sigap-'.$laporan->dari->format('Ymd').'-'.$laporan->sampai->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($laporan) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($keluaran, [
                'Kode', 'Dibuat', 'Pasien', 'Jenis layanan', 'Tingkat darurat', 'Status', 'Lokasi jemput',
                'Faskes tujuan', 'Faskes asal', 'Ambulans', 'Sopir', 'Respons (menit)', 'Durasi layanan (menit)',
            ]);

            foreach ($laporan->baris() as $p) {
                fputcsv($keluaran, array_map([$this, 'aman'], [
                    $p->kode,
                    $p->created_at->format('Y-m-d H:i'),
                    $p->nama_pasien,
                    $p->jenis_layanan->label(),
                    $p->tingkat_darurat->label(),
                    $p->status->label(),
                    $p->lokasi_jemput,
                    $p->faskesTujuan?->nama,
                    $p->ambulans?->faskes->nama,
                    $p->ambulans?->plat_nomor,
                    $p->sopir?->name,
                    $this->menit($p, 'respons'),
                    $this->menit($p, 'durasi'),
                ]));
            }
            fclose($keluaran);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Cegah rumus spreadsheet dari teks yang diketik pengguna (diawali = + - @). */
    private function aman(mixed $nilai): string
    {
        $teks = (string) ($nilai ?? '');

        return $teks !== '' && str_contains('=+-@', $teks[0]) ? "'".$teks : $teks;
    }

    private function menit(Pesanan $p, string $jenis): string
    {
        $detik = $jenis === 'respons' ? LaporanPesanan::responsDetik($p) : LaporanPesanan::durasiDetik($p);

        return $detik === null ? '' : (string) round($detik / 60, 1);
    }
}
