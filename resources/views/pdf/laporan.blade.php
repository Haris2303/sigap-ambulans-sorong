<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan SIGAP Ambulans</title>
    <style>
        /* dompdf tidak memuat Tailwind atau variabel CSS: nilai di bawah adalah salinan statis token DESIGN.md (dijaga TokenDesainTest). Font aplikasi (Plus Jakarta Sans) tidak tersedia di dompdf, jadi DejaVu Sans. */
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1B2437; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        h2 { font-size: 13px; margin: 18px 0 6px 0; }
        p { margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #D0D6E4; padding: 4px 6px; text-align: left; }
        th { background: #F7F8FD; }
        .angka { text-align: right; }
        .muted { color: #5F6B80; }
    </style>
</head>
<body>
    <h1>Laporan SIGAP Ambulans</h1>
    <p>Dinas Kesehatan Kota Sorong</p>
    <p class="muted">Periode {{ $data['dari']->translatedFormat('d M Y') }} sampai {{ $data['sampai']->translatedFormat('d M Y') }}. Dibuat {{ $dibuat->translatedFormat('d M Y H:i') }}.</p>

    <h2>Ringkasan</h2>
    <table>
        <tr><th>Total pesanan</th><td class="angka">{{ $data['total'] }}</td></tr>
        <tr><th>Rata-rata waktu respons</th><td class="angka">{{ \App\Services\LaporanPesanan::formatDurasi($data['respons']['rata']) }}</td></tr>
        <tr><th>Rata-rata durasi layanan</th><td class="angka">{{ \App\Services\LaporanPesanan::formatDurasi($data['durasi']['rata']) }}</td></tr>
    </table>

    <h2>Pesanan per status</h2>
    <table>
        <tr><th>Status</th><th class="angka">Jumlah</th></tr>
        @foreach ($data['per_status'] as $label => $jumlah)
            <tr><td>{{ $label }}</td><td class="angka">{{ $jumlah }}</td></tr>
        @endforeach
    </table>

    <h2>Pesanan per jenis layanan</h2>
    <table>
        <tr><th>Jenis layanan</th><th class="angka">Jumlah</th></tr>
        @foreach ($data['per_jenis'] as $label => $jumlah)
            <tr><td>{{ $label }}</td><td class="angka">{{ $jumlah }}</td></tr>
        @endforeach
    </table>

    <h2>Penugasan per Faskes asal</h2>
    @if (empty($data['per_faskes']))
        <p class="muted">Belum ada penugasan pada periode ini.</p>
    @else
        <table>
            <tr><th>Faskes</th><th class="angka">Jumlah</th></tr>
            @foreach ($data['per_faskes'] as $nama => $jumlah)
                <tr><td>{{ $nama }}</td><td class="angka">{{ $jumlah }}</td></tr>
            @endforeach
        </table>
    @endif

    <h2>Waktu respons dan durasi layanan</h2>
    <table>
        <tr><th>Ukuran</th><th>Respons</th><th>Durasi layanan</th></tr>
        @foreach (['rata' => 'Rata-rata', 'tercepat' => 'Tercepat', 'terlama' => 'Terlama'] as $kunci => $label)
            <tr>
                <td>{{ $label }}</td>
                <td>{{ \App\Services\LaporanPesanan::formatDurasi($data['respons'][$kunci]) }}</td>
                <td>{{ \App\Services\LaporanPesanan::formatDurasi($data['durasi'][$kunci]) }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
