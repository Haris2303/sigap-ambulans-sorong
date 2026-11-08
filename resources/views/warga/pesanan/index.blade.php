{{-- Riwayat pesanan Masyarakat arah B (docs/RETEMA_B.md 8.2): pola daftar yang sama, tanpa tab dan cari (belum ada filter sebelumnya). --}}
<x-layouts.app judul="Riwayat pesanan">
    <x-ui.judul-halaman judul="Riwayat pesanan" deskripsi="Semua pesanan ambulans yang pernah Anda buat.">
        <x-slot:aksi>
            <x-ui.tombol :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.panel :flush="true">
        <div class="px-5 pt-5 pb-5 lg:px-6 lg:pt-6 lg:pb-6">
            @include('pesanan._baris', [
                'daftar' => $daftar,
                'rute' => 'warga.pesanan.show',
                'pesanKosong' => 'Belum ada pesanan.',
                'kosongAksiHref' => route('warga.pesanan.buat'),
                'kosongAksiLabel' => 'Pesan ambulans',
            ])
        </div>
    </x-ui.panel>

    @if ($daftar->isNotEmpty())
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
