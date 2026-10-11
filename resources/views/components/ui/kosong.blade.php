@props(['pesan', 'judul' => null, 'ikon' => null])
{{-- Keadaan kosong arah B (DESIGN.md 6.11, x-ui.empty): batas putus-putus, pusat, padding 22px. judul (label-l) dan ikon
     (petak 44px) opsional; pesan (body-s on-surface-variant) selalu ada dan mengarahkan tindakan. --}}
<div {{ $attributes->merge(['class' => 'rounded-md border border-dashed border-outline-variant p-5.5 text-center']) }}>
    @if ($ikon)
        <span class="mx-auto mb-3 inline-flex size-11 items-center justify-center rounded-md bg-surface-container text-on-surface-variant">
            <x-ui.ikon :nama="$ikon" :ukuran="22" />
        </span>
    @endif
    @if ($judul)
        <p class="text-label-l">{{ $judul }}</p>
    @endif
    <p class="text-body-s text-on-surface-variant {{ $judul ? 'mt-1' : '' }}">{{ $pesan }}</p>
    @isset($aksi)
        <div class="mt-4">{{ $aksi }}</div>
    @endisset
</div>
