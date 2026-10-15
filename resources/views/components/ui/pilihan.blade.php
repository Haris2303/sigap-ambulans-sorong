@props(['nama', 'label', 'opsi', 'nilai' => null, 'kosong' => null, 'bantuan' => null, 'wajib' => false, 'galat' => null, 'labelTersembunyi' => false])
{{-- $opsi: nilai => label, atau judul kelompok => [nilai => label] (dirender sebagai optgroup). appearance-none dengan
     ikon chevron-down 18px di kanan (DESIGN.md 6.3). --}}
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = $galat ?? ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama);
    $terpilih = (string) old($nama, $nilai instanceof \BackedEnum ? $nilai->value : $nilai);
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="{{ $labelTersembunyi ? 'sr-only' : \App\Support\Tampilan::LABEL_ISIAN }}">{{ $label }}@if ($wajib) (wajib)@endif</label>
    <div class="relative">
        <select id="{{ $id }}" name="{{ $nama }}"
            @if ($wajib) required @endif
            @if ($galat) aria-invalid="true" @endif
            @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
            {{ $attributes->merge(['class' => \App\Support\Tampilan::kelasIsian((bool) $galat).' appearance-none pr-10']) }}>
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
        <x-ui.ikon nama="chevron-down" :ukuran="18" class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-on-surface-variant" />
    </div>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="{{ \App\Support\Tampilan::BANTUAN_ISIAN }}">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="{{ \App\Support\Tampilan::GALAT_ISIAN }}">{{ $galat }}</p>
    @endif
</div>
