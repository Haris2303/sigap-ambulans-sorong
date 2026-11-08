@props(['varian' => 'tombol'])
{{-- Jalur cadangan lewat telepon bila aplikasi tidak dapat dipakai (App\Support\KontakDarurat). Nomor dari config('sigap.kontak_darurat'); tanpa nomor tidak menampilkan apa pun.
     varian: tombol (sekunder 48px, ikon phone) atau tautan (kalimat dengan tautan tel:). Slot: kalimat pengantar, mis. "Keadaan darurat?". --}}
@php
    $kontak = \App\Support\KontakDarurat::data();
    $teks = $kontak ? 'Hubungi '.$kontak['nama'].' di '.$kontak['nomor'] : '';
@endphp
@if ($kontak)
    @if ($varian === 'tautan')
        <p {{ $attributes->merge(['class' => 'text-body-m text-on-surface-variant']) }}>
            @if (trim((string) $slot) !== '') {{ $slot }} @endif
            <a href="tel:{{ $kontak['tel'] }}" class="lapisan-keadaan target-sentuh rounded-xs px-1 font-medium tabular-nums text-primary underline underline-offset-2">{{ $teks }}</a>.
        </p>
    @else
        <div {{ $attributes }}>
            @if (trim((string) $slot) !== '')
                <p class="mb-2 text-body-m text-on-surface-variant">{{ $slot }}</p>
            @endif
            <x-ui.tombol nada="sekunder" ikon="phone" :href="'tel:'.$kontak['tel']">{{ $teks }}</x-ui.tombol>
        </div>
    @endif
@endif
