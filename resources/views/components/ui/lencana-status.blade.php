@props(['status'])
{{-- PesananStatus::tone() adalah sumber tunggal warna lencana status (DESIGN.md 4.3, 6.6). --}}
<x-ui.lencana :nada="$status->tone()" {{ $attributes }}>{{ $status->label() }}</x-ui.lencana>
