@props(['nama', 'label', 'nilai' => null, 'bantuan' => null, 'wajib' => false, 'baris' => 3, 'galat' => null])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = $galat ?? ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama);
    $isi = old($nama, $nilai);
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="{{ \App\Support\Tampilan::LABEL_ISIAN }}">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $nama }}" rows="{{ $baris }}"
        @if ($wajib) required @endif
        @if ($galat) aria-invalid="true" @endif
        @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
        {{ $attributes->merge(['class' => \App\Support\Tampilan::kelasIsian((bool) $galat).' py-3']) }}>{{ $isi }}</textarea>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="{{ \App\Support\Tampilan::BANTUAN_ISIAN }}">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="{{ \App\Support\Tampilan::GALAT_ISIAN }}">{{ $galat }}</p>
    @endif
</div>
