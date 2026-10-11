@props(['data', 'nada' => 'info'])
@php
    $maks = max(1, (int) collect($data)->max());
@endphp
{{-- Satu-satunya tempat yang boleh memakai style= (lebar batang dinamis). Label berlebar tetap tanpa pemecahan baris; nilai rata kanan. --}}
<ul {{ $attributes->merge(['class' => 'space-y-2']) }} role="list">
    @foreach ($data as $label => $angka)
        <li class="flex items-center gap-3 text-body-m">
            <span class="w-24 shrink-0 whitespace-nowrap text-body-s text-on-surface-variant">{{ $label }}</span>
            <span class="h-3 flex-1 overflow-hidden rounded-sm bg-surface-container-highest">
                <span class="block h-3 rounded-sm {{ \App\Support\Tampilan::kelasLatar($nada) }}" style="width: {{ round($angka / $maks * 100) }}%"></span>
            </span>
            <span class="w-10 shrink-0 text-right tabular-nums">{{ $angka }}</span>
        </li>
    @endforeach
</ul>
