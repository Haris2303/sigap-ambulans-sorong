@props(['faskes'])
<div {{ $attributes->merge(['class' => 'text-body-m']) }}>
    <dl class="grid grid-cols-2 gap-x-4 gap-y-1">
        <dt class="text-on-surface-variant">Bed IGD</dt>
        <dd class="tabular-nums">{{ $faskes->bed_igd_tersedia }}/{{ $faskes->bed_igd_total }}</dd>
        <dt class="text-on-surface-variant">Bed rawat inap</dt>
        <dd class="tabular-nums">{{ $faskes->bed_rawat_inap_tersedia }}/{{ $faskes->bed_rawat_inap_total }}</dd>
        <dt class="text-on-surface-variant">Dokter umum</dt>
        <dd class="tabular-nums">{{ $faskes->dokter_umum_tersedia }}</dd>
        <dt class="text-on-surface-variant">Dokter spesialis</dt>
        <dd class="tabular-nums">{{ $faskes->dokter_spesialis_tersedia }}</dd>
        <dt class="text-on-surface-variant">Ambulans tersedia</dt>
        <dd class="tabular-nums">{{ $faskes->ambulans_tersedia_count ?? '-' }}</dd>
    </dl>
    <x-ui.pembaruan :faskes="$faskes" class="mt-2" />
</div>
