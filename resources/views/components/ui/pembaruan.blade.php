@props(['faskes'])
{{-- Keterangan kesegaran data ketersediaan Faskes: "Belum diperbarui", atau "Diperbarui ..." dengan lencana "Data lama" bila basi. --}}
<p {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-body-s text-on-surface-variant']) }}>
    @if ($faskes->belumDiperbarui())
        <x-ui.lencana nada="peringatan">Belum diperbarui</x-ui.lencana>
    @else
        <span>Diperbarui <x-ui.waktu :nilai="$faskes->ketersediaan_diperbarui_pada" :relatif="true" /></span>
        @if ($faskes->dataLama())
            <x-ui.lencana nada="peringatan">Data lama</x-ui.lencana>
        @endif
    @endif
</p>
