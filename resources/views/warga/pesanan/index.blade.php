<x-layouts.app judul="Riwayat pesanan">
    <x-ui.judul-halaman judul="Riwayat pesanan" deskripsi="Semua pesanan ambulans yang pernah Anda buat.">
        <x-slot:aksi>
            <x-ui.tombol :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    @if ($daftar->isEmpty())
        <x-ui.kosong pesan="Belum ada pesanan.">
            <x-slot:aksi>
                <x-ui.tombol nada="sekunder" :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
            </x-slot:aksi>
        </x-ui.kosong>
    @else
        @include('warga._tabel-pesanan', ['daftar' => $daftar])
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
