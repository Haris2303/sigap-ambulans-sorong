@props(['nama', 'label', 'opsi', 'nilai' => null, 'kosong' => null, 'bantuan' => null, 'wajib' => false])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $galat = ($errors ?? new \Illuminate\Support\ViewErrorBag)->first($nama);
    $terpilih = (string) old($nama, $nilai instanceof \BackedEnum ? $nilai->value : $nilai);
    $deskripsi = collect([$bantuan ? $id.'-bantuan' : null, $galat ? $id.'-galat' : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="mb-1 block text-label text-on-surface">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $nama }}"
        @if ($wajib) required @endif
        @if ($galat) aria-invalid="true" @endif
        @if ($deskripsi !== '') aria-describedby="{{ $deskripsi }}" @endif
        {{ $attributes->merge(['class' => 'block w-full min-h-touch rounded-md border bg-surface px-3 text-body text-on-surface '.($galat ? 'border-danger' : 'border-control-border')]) }}>
        @if ($kosong !== null)
            <option value="">{{ $kosong }}</option>
        @endif
        @foreach ($opsi as $nilaiOpsi => $labelOpsi)
            <option value="{{ $nilaiOpsi }}" @selected($terpilih !== '' && (string) $nilaiOpsi === $terpilih)>{{ $labelOpsi }}</option>
        @endforeach
    </select>
    @if ($bantuan)
        <p id="{{ $id }}-bantuan" class="mt-1 text-caption text-on-surface-muted">{{ $bantuan }}</p>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="mt-1 text-body-sm text-danger">{{ $galat }}</p>
    @endif
</div>
