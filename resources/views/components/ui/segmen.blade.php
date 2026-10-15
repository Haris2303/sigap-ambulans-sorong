@props(['nama', 'nilai', 'terpilih' => false])
{{-- Satu segmen di dalam x-ui.segmented (DESIGN.md 6.5): radio tersembunyi, label berperan sebagai segmen 36px. --}}
@php
    $dipilih = old($nama) !== null ? (string) old($nama) === (string) $nilai : (bool) $terpilih;
@endphp
<label class="lapisan-keadaan target-sentuh has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary flex min-h-9 cursor-pointer items-center justify-center rounded-sm px-4 text-label-m transition-colors {{ $dipilih ? 'bg-surface-container-lowest text-on-surface shadow-chip' : 'text-on-surface-variant' }}">
    <input type="radio" name="{{ $nama }}" value="{{ $nilai }}" @checked($dipilih) class="sr-only">
    {{ $slot }}
</label>
