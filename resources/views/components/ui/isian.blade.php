@props(['nama', 'label', 'tipe' => 'text', 'nilai' => null, 'bantuan' => null, 'wajib' => false, 'galat' => null])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = $galat ?? ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama);
    $isi = $tipe === 'password' ? null : old($nama, $nilai instanceof \BackedEnum ? $nilai->value : $nilai);
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="mb-1 block text-label text-on-surface">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $nama }}" type="{{ $tipe }}" @if ($isi !== null) value="{{ $isi }}" @endif
        @if ($wajib) required @endif
        @if ($galat) aria-invalid="true" @endif
        @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
        {{ $attributes->merge(['class' => 'block w-full min-h-touch rounded-md border bg-surface px-3 text-body text-on-surface placeholder:text-on-surface-muted '.($galat ? 'border-danger' : 'border-control-border')]) }}>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="mt-1 text-caption text-on-surface-muted">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="mt-1 text-body-sm text-danger">{{ $galat }}</p>
    @endif
</div>
