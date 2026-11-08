<x-layouts.auth judul="Sesi berakhir">
    <x-ui.judul-halaman judul="Sesi berakhir" deskripsi="Halaman terlalu lama terbuka. Muat ulang halaman, lalu ulangi tindakan Anda." />
    <x-ui.tombol :href="route('beranda')">Ke beranda</x-ui.tombol>
    <x-ui.kontak-darurat class="mt-6">Keadaan darurat?</x-ui.kontak-darurat>
</x-layouts.auth>
