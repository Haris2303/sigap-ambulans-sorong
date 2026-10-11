@props(['faskes'])
<div {{ $attributes->merge(['class' => 'text-body-sm']) }}>
    <dl class="grid grid-cols-2 gap-x-4 gap-y-1">
        <dt class="text-on-surface-muted">Bed IGD</dt>
        <dd class="tabular-nums">{{ $faskes->bed_igd_tersedia }}/{{ $faskes->bed_igd_total }}</dd>
        <dt class="text-on-surface-muted">Bed rawat inap</dt>
        <dd class="tabular-nums">{{ $faskes->bed_rawat_inap_tersedia }}/{{ $faskes->bed_rawat_inap_total }}</dd>
        <dt class="text-on-surface-muted">Dokter umum</dt>
        <dd class="tabular-nums">{{ $faskes->dokter_umum_tersedia }}</dd>
        <dt class="text-on-surface-muted">Dokter spesialis</dt>
        <dd class="tabular-nums">{{ $faskes->dokter_spesialis_tersedia }}</dd>
        <dt class="text-on-surface-muted">Ambulans tersedia</dt>
        <dd class="tabular-nums">{{ $faskes->ambulans_tersedia_count ?? '-' }}</dd>
    </dl>
    <p class="mt-2 flex flex-wrap items-center gap-2 text-caption text-on-surface-muted">
        @if ($faskes->belumDiperbarui())
            <x-ui.lencana nada="peringatan">Belum diperbarui</x-ui.lencana>
        @else
            <span>Diperbarui <x-ui.waktu :nilai="$faskes->ketersediaan_diperbarui_pada" :relatif="true" /></span>
            @if ($faskes->dataLama())
                <x-ui.lencana nada="peringatan">Data lama</x-ui.lencana>
            @endif
        @endif
    </p>
</div>
