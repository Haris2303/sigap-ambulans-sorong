<x-layouts.app judul="Beranda">
    <x-ui.judul-halaman judul="Beranda" :deskripsi="'Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:aksi>
            <x-ui.tombol :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.ringkasan>
        <x-ui.ringkasan-sel label="Pesanan aktif" :nilai="$aktif->count()" nada="info" />
        <x-ui.ringkasan-sel label="Pesanan selesai" :nilai="$jumlahSelesai" nada="sukses" keterangan="Dari {{ $jumlahSemua }} pesanan" />
    </x-ui.ringkasan>

    <x-ui.bagian judul="Pesanan aktif">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ukuran="sm" :href="route('warga.pesanan.index')">Riwayat pesanan</x-ui.tombol>
        </x-slot:aksi>
        @if ($aktif->isEmpty())
            <x-ui.kosong pesan="Belum ada pesanan aktif.">
                <x-slot:aksi>
                    <x-ui.tombol nada="sekunder" :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
                </x-slot:aksi>
            </x-ui.kosong>
        @else
            @include('warga._tabel-pesanan', ['daftar' => $aktif])
        @endif
    </x-ui.bagian>
</x-layouts.app>
