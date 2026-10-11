@props(['nama', 'label', 'nilai' => null, 'bantuan' => null, 'wajib' => false, 'baris' => 3])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama);
    $isi = old($nama, $nilai);
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="mb-1 block text-label text-on-surface">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $nama }}" rows="{{ $baris }}"
        @if ($wajib) required @endif
        @if ($galat) aria-invalid="true" @endif
        @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
        {{ $attributes->merge(['class' => 'block w-full min-h-touch rounded-md border bg-surface px-3 py-2 text-body text-on-surface placeholder:text-on-surface-muted '.($galat ? 'border-danger' : 'border-control-border')]) }}>{{ $isi }}</textarea>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="mt-1 text-caption text-on-surface-muted">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="mt-1 text-body-sm text-danger">{{ $galat }}</p>
    @endif
</div>
