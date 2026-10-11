<x-layouts.app judul="Pesanan">
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Pesanan gawat dan yang paling lama menunggu tampil paling atas." />

    <form method="GET" action="{{ route('admin.pesanan.index') }}" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.pilihan nama="status" label="Status" :opsi="\App\Enums\PesananStatus::opsi()" :nilai="$filter['status'] ?? null" kosong="Semua status" />
        <x-ui.pilihan nama="jenis_layanan" label="Jenis layanan" :opsi="\App\Enums\JenisLayanan::opsi()" :nilai="$filter['jenis_layanan'] ?? null" kosong="Semua jenis" />
        <x-ui.isian nama="dari" label="Dari tanggal" tipe="date" :nilai="$filter['dari'] ?? null" />
        <x-ui.isian nama="sampai" label="Sampai tanggal" tipe="date" :nilai="$filter['sampai'] ?? null" />
        <x-ui.isian nama="cari" label="Kata kunci" :nilai="$filter['cari'] ?? null" placeholder="Kode, pasien, atau lokasi" />
        <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-5">
            <x-ui.tombol nada="sekunder" tipe="submit" ikon="search">Cari</x-ui.tombol>
            @if ($adaFilter)
                <x-ui.tombol nada="hantu" :href="route('admin.pesanan.index')">Atur ulang</x-ui.tombol>
            @endif
        </div>
    </form>

    @if ($daftar->isEmpty())
        <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan.'" />
    @else
        @include('admin._tabel-pesanan', ['daftar' => $daftar])
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
