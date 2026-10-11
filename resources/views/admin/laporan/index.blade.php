<x-layouts.app judul="Laporan">
    <x-ui.judul-halaman judul="Laporan" deskripsi="Rekap pesanan berdasarkan tanggal pesanan dibuat.">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ikon="file-text" :href="route('admin.laporan.pdf', ['dari' => $dari, 'sampai' => $sampai])">Unduh PDF</x-ui.tombol>
            <x-ui.tombol nada="sekunder" ikon="download" :href="route('admin.laporan.csv', ['dari' => $dari, 'sampai' => $sampai])">Unduh CSV</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <form method="GET" action="{{ route('admin.laporan.index') }}" class="mb-6 grid gap-3 sm:grid-cols-3">
        <x-ui.isian nama="dari" label="Dari tanggal" tipe="date" :nilai="$dari" :wajib="true" />
        <x-ui.isian nama="sampai" label="Sampai tanggal" tipe="date" :nilai="$sampai" :wajib="true" />
        <div class="flex items-end">
            <x-ui.tombol nada="sekunder" tipe="submit">Tampilkan</x-ui.tombol>
        </div>
    </form>

    <x-ui.ringkasan>
        <x-ui.ringkasan-sel label="Total pesanan" :nilai="$data['total']" />
        <x-ui.ringkasan-sel label="Selesai" :nilai="$data['per_status'][\App\Enums\PesananStatus::Selesai->label()]" nada="sukses" />
        <x-ui.ringkasan-sel label="Rata-rata waktu respons" :nilai="\App\Services\LaporanPesanan::formatDurasi($data['respons']['rata'])" :keterangan="$data['respons']['jumlah'].' pesanan ditugaskan'" />
        <x-ui.ringkasan-sel label="Rata-rata durasi layanan" :nilai="\App\Services\LaporanPesanan::formatDurasi($data['durasi']['rata'])" :keterangan="$data['durasi']['jumlah'].' pesanan selesai'" />
    </x-ui.ringkasan>

    <x-ui.bagian judul="Pesanan per status">
        <x-ui.tabel>
            <x-slot:kepala><th>Status</th><th class="text-right">Jumlah</th></x-slot:kepala>
            @foreach ($data['per_status'] as $label => $jumlah)
                <tr><td>{{ $label }}</td><td class="text-right tabular-nums">{{ $jumlah }}</td></tr>
            @endforeach
        </x-ui.tabel>
    </x-ui.bagian>

    <x-ui.bagian judul="Pesanan per jenis layanan">
        <x-ui.tabel>
            <x-slot:kepala><th>Jenis layanan</th><th class="text-right">Jumlah</th></x-slot:kepala>
            @foreach ($data['per_jenis'] as $label => $jumlah)
                <tr><td>{{ $label }}</td><td class="text-right tabular-nums">{{ $jumlah }}</td></tr>
            @endforeach
        </x-ui.tabel>
    </x-ui.bagian>

    <x-ui.bagian judul="Penugasan per Faskes asal">
        @if (empty($data['per_faskes']))
            <x-ui.kosong pesan="Belum ada penugasan pada periode ini." />
        @else
            <x-ui.tabel>
                <x-slot:kepala><th>Faskes</th><th class="text-right">Jumlah</th></x-slot:kepala>
                @foreach ($data['per_faskes'] as $nama => $jumlah)
                    <tr><td>{{ $nama }}</td><td class="text-right tabular-nums">{{ $jumlah }}</td></tr>
                @endforeach
            </x-ui.tabel>
        @endif
    </x-ui.bagian>

    <x-ui.bagian judul="Waktu respons dan durasi layanan" deskripsi="Respons dihitung dari pesanan dibuat sampai ditugaskan. Durasi dihitung dari ditugaskan sampai selesai.">
        <x-ui.tabel>
            <x-slot:kepala><th>Ukuran</th><th>Respons</th><th>Durasi layanan</th></x-slot:kepala>
            @foreach (['rata' => 'Rata-rata', 'tercepat' => 'Tercepat', 'terlama' => 'Terlama'] as $kunci => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="tabular-nums">{{ \App\Services\LaporanPesanan::formatDurasi($data['respons'][$kunci]) }}</td>
                    <td class="tabular-nums">{{ \App\Services\LaporanPesanan::formatDurasi($data['durasi'][$kunci]) }}</td>
                </tr>
            @endforeach
        </x-ui.tabel>
    </x-ui.bagian>
</x-layouts.app>
