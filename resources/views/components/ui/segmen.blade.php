@props(['nama', 'nilai', 'terpilih' => false])
{{-- Satu segmen di dalam x-ui.segmented (DESIGN.md 6.5): radio tersembunyi, label berperan sebagai segmen 36px.
     Gaya terpilih lewat has-[:checked], bukan kelas kondisional PHP: tanpa ini klik radio mengubah status tapi
     tampilan tetap diam sampai muat ulang halaman (ditemukan lewat uji browser nyata). --}}
@php
    $dipilih = old($nama) !== null ? (string) old($nama) === (string) $nilai : (bool) $terpilih;
@endphp
<label class="lapisan-keadaan target-sentuh has-[:checked]:bg-surface-container-lowest has-[:checked]:text-on-surface has-[:checked]:shadow-chip has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary flex min-h-9 cursor-pointer items-center justify-center rounded-sm px-4 text-label-m text-on-surface-variant transition-colors">
    <input type="radio" name="{{ $nama }}" value="{{ $nilai }}" @checked($dipilih) class="sr-only">
    {{ $slot }}
</label>
