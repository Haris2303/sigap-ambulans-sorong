@props(['nama', 'deskripsi' => null])
{{-- Kepala halaman bersapaan (docs/RETEMA_B.md 5.1): "Selamat {pagi|siang|sore|malam}, {nama}" menurut jam
     Asia/Jayapura. Lapisan tampilan murni (tidak membaca apa pun dari controller selain nama); dipakai Beranda
     warga serta dasbor Faskes dan Sopir supaya sapaan tidak lagi diduplikasi di tiap halaman (utang dari P2).
     Membungkus x-ui.judul-halaman, meneruskan slot aksi bila ada. --}}
@php
    $jam = (int) now()->timezone(config('app.timezone'))->format('G');
    $kata = match (true) {
        $jam < 11 => 'pagi',
        $jam < 15 => 'siang',
        $jam < 18 => 'sore',
        default => 'malam',
    };
@endphp
<x-ui.judul-halaman {{ $attributes }} :judul="'Selamat '.$kata.', '.$nama" :deskripsi="$deskripsi">
    @isset($aksi)
        <x-slot:aksi>{{ $aksi }}</x-slot:aksi>
    @endisset
</x-ui.judul-halaman>
