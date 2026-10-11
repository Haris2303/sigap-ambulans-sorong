<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.laporan.index', [
            'data' => $laporan->ringkas(),
            'dari' => $laporan->dari->toDateString(),
            'sampai' => $laporan->sampai->toDateString(),
        ]);
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
