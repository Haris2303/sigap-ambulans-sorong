<x-layouts.auth judul="Akses ditolak">
    <x-ui.judul-halaman judul="Akses ditolak" deskripsi="Akun Anda tidak berhak membuka halaman ini. Kembali ke beranda atau masuk dengan akun lain." />
    <x-ui.tombol :href="route('beranda')">Ke beranda</x-ui.tombol>
</x-layouts.auth>
