@props(['nama', 'nilai', 'judul', 'deskripsi' => null, 'terpilih' => null])
@php
    $dipilih = old($nama) !== null ? (string) old($nama) === (string) $nilai : (bool) $terpilih;
@endphp
<label class="flex min-h-touch cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft {{ $dipilih ? 'border-primary bg-primary-soft' : 'border-control-border bg-surface' }}">
    <input type="radio" name="{{ $nama }}" value="{{ $nilai }}" @checked($dipilih) class="mt-0.5 size-5 shrink-0 accent-primary">
    <span class="min-w-0">
        <span class="block text-h3">{{ $judul }}</span>
        @if ($deskripsi)
            <span class="mt-0.5 block text-body-sm text-on-surface-muted">{{ $deskripsi }}</span>
        @endif
    </span>
</label>
