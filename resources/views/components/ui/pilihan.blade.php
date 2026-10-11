@props(['nama', 'label', 'opsi', 'nilai' => null, 'kosong' => null, 'bantuan' => null, 'wajib' => false, 'galat' => null])
{{-- $opsi: nilai => label, atau judul kelompok => [nilai => label] (dirender sebagai optgroup). --}}
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = $galat ?? ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama);
    $terpilih = (string) old($nama, $nilai instanceof \BackedEnum ? $nilai->value : $nilai);
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="{{ \App\Support\Tampilan::LABEL_ISIAN }}">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $nama }}"
        @if ($wajib) required @endif
        @if ($galat) aria-invalid="true" @endif
        @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
        {{ $attributes->merge(['class' => \App\Support\Tampilan::kelasIsian((bool) $galat)]) }}>
        @if ($kosong !== null)
            <option value="">{{ $kosong }}</option>
        @endif
        @foreach ($opsi as $kunci => $isi)
            @if (is_array($isi))
                <optgroup label="{{ $kunci }}">
                    @foreach ($isi as $nilaiOpsi => $labelOpsi)
                        <option value="{{ $nilaiOpsi }}" @selected($terpilih !== '' && (string) $nilaiOpsi === $terpilih)>{{ $labelOpsi }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $kunci }}" @selected($terpilih !== '' && (string) $kunci === $terpilih)>{{ $isi }}</option>
            @endif
        @endforeach
    </select>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="{{ \App\Support\Tampilan::BANTUAN_ISIAN }}">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="{{ \App\Support\Tampilan::GALAT_ISIAN }}">{{ $galat }}</p>
    @endif
</div>
