@props(['nama', 'nilai', 'judul', 'deskripsi' => null, 'terpilih' => null])
@php
    $dipilih = old($nama) !== null ? (string) old($nama) === (string) $nilai : (bool) $terpilih;
@endphp
<label class="lapisan-keadaan flex min-h-14 cursor-pointer items-start gap-3 rounded-md border p-4 transition-colors has-[:checked]:border-secondary-container has-[:checked]:bg-secondary-container has-[:checked]:text-on-secondary-container {{ $dipilih ? 'border-secondary-container bg-secondary-container text-on-secondary-container' : 'border-outline text-on-surface' }}">
    <input type="radio" name="{{ $nama }}" value="{{ $nilai }}" @checked($dipilih) class="mt-0.5 size-5 shrink-0 accent-primary">
    <span class="min-w-0">
        <span class="block text-title-m">{{ $judul }}</span>
        @if ($deskripsi)
            <span class="mt-0.5 block text-body-m text-on-surface-variant">{{ $deskripsi }}</span>
        @endif
    </span>
</label>
