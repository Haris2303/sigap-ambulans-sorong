@props(['lat', 'lng', 'urlPosisi' => null, 'tinggi' => 'h-72'])
@php
    $peta = config('sigap.peta');
@endphp
<div {{ $attributes }} data-peta="pelacak" data-lat="{{ $lat }}" data-lng="{{ $lng }}"
    @if ($urlPosisi) data-url-posisi="{{ $urlPosisi }}" @endif
    data-detik="{{ config('sigap.polling.warga_detik') }}" data-osrm="{{ $peta['osrm_url'] }}/driving">
    <div data-peta-kanvas class="isolate {{ $tinggi }} rounded-lg border border-line bg-neutral-soft"
        data-ubin="{{ $peta['ubin_url'] }}" data-pusat-lat="{{ $lat }}" data-pusat-lng="{{ $lng }}" data-zoom="15"></div>
</div>
