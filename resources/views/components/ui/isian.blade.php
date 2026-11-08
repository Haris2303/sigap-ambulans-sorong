@props(['nama', 'label', 'tipe' => 'text', 'nilai' => null, 'bantuan' => null, 'wajib' => false, 'galat' => null, 'labelTersembunyi' => false, 'tanpaOld' => false, 'idUnik' => null])
{{-- tanpaOld: abaikan old() dan galat sesi (baris tabel yang memakai nama kolom sama). labelTersembunyi: label hanya untuk pembaca layar.
     Tanda wajib ditulis dalam label ("(wajib)"), bukan bintang berwarna (DESIGN.md 6.3). --}}
@php
    $id = $idUnik ?? 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = $galat ?? ($tanpaOld ? null : ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama));
    $nilaiAwal = $nilai instanceof \BackedEnum ? $nilai->value : $nilai;
    $isi = $tipe === 'password' ? null : ($tanpaOld ? $nilaiAwal : old($nama, $nilaiAwal));
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="{{ $labelTersembunyi ? 'sr-only' : \App\Support\Tampilan::LABEL_ISIAN }}">{{ $label }}@if ($wajib) (wajib)@endif</label>
    <input id="{{ $id }}" name="{{ $nama }}" type="{{ $tipe }}" @if ($isi !== null) value="{{ $isi }}" @endif
        @if ($wajib) required @endif
        @if ($galat) aria-invalid="true" @endif
        @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
        {{ $attributes->merge(['class' => \App\Support\Tampilan::kelasIsian((bool) $galat)]) }}>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="{{ \App\Support\Tampilan::BANTUAN_ISIAN }}">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="{{ \App\Support\Tampilan::GALAT_ISIAN }}">{{ $galat }}</p>
    @endif
</div>
