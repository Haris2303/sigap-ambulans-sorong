<x-layouts.auth judul="Terjadi kesalahan">
    <x-ui.judul-halaman judul="Terjadi kesalahan" deskripsi="Permintaan Anda tidak dapat diproses. Coba lagi beberapa saat, atau hubungi Dinkes bila masalah berlanjut." />
    <x-ui.tombol :href="route('beranda')">Ke beranda</x-ui.tombol>
    <x-ui.kontak-darurat class="mt-6">Keadaan darurat?</x-ui.kontak-darurat>
</x-layouts.auth>
