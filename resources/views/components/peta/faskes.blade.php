@props(['daftar', 'tinggi' => 'h-80'])
@php
    $peta = config('sigap.peta');
    $titik = collect($daftar)
        ->filter(fn ($f) => $f->punyaKoordinat())
        ->map(fn ($f) => ['nama' => $f->nama, 'jenis' => $f->jenis->label(), 'lat' => (float) $f->lat, 'lng' => (float) $f->lng])
        ->values();
@endphp
@if ($titik->isEmpty())
    <x-ui.kosong pesan="Belum ada Faskes dengan titik koordinat." />
@else
    <div {{ $attributes }} data-peta="faskes" data-daftar="{{ json_encode($titik) }}">
        <div data-peta-kanvas class="isolate {{ $tinggi }} rounded-lg border border-line bg-neutral-soft"
            data-ubin="{{ $peta['ubin_url'] }}" data-pusat-lat="{{ $peta['pusat_lat'] }}" data-pusat-lng="{{ $peta['pusat_lng'] }}" data-zoom="{{ $peta['zoom'] }}"></div>
    </div>
@endif
