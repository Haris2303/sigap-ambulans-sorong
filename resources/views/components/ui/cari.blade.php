@props(['aksi', 'label', 'nama' => 'q', 'nilai' => null, 'placeholder' => null, 'id' => null])
{{-- Kolom cari berbentuk pil 48px (DESIGN.md search-field): form GET ke daftar dengan ?{nama}=. Nilai bawaan diambil dari query yang sedang aktif. --}}
@php
    $id ??= 'cari-'.$nama;
    $isi = $nilai ?? request()->query($nama);
    $isi = is_scalar($isi) ? (string) $isi : '';
@endphp
<form method="GET" action="{{ $aksi }}" role="search" {{ $attributes->merge(['class' => 'flex h-12 min-w-0 flex-1 items-center gap-3 rounded-full bg-surface-container px-4 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary']) }}>
    <x-ui.ikon nama="search" :ukuran="22" class="text-on-surface-variant" />
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <input id="{{ $id }}" type="search" name="{{ $nama }}" @if ($isi !== '') value="{{ $isi }}" @endif @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        class="min-w-0 flex-1 bg-transparent text-body-l text-on-surface outline-hidden placeholder:text-on-surface-variant">
</form>
