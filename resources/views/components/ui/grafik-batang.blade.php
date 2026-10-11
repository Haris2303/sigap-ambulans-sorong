@props(['data', 'nada' => 'info'])
@php
    $maks = max(1, (int) collect($data)->max());
@endphp
{{-- Satu-satunya tempat yang boleh memakai style= (lebar batang dinamis). --}}
<ul {{ $attributes->merge(['class' => 'space-y-2']) }} role="list">
    @foreach ($data as $label => $angka)
        <li class="flex items-center gap-3 text-body-sm">
            <span class="w-20 shrink-0 text-caption text-on-surface-muted">{{ $label }}</span>
            <span class="h-3 flex-1 overflow-hidden rounded-sm bg-neutral-soft">
                <span class="block h-3 rounded-sm {{ \App\Support\Tampilan::kelasLatar($nada) }}" style="width: {{ round($angka / $maks * 100) }}%"></span>
            </span>
            <span class="w-8 shrink-0 text-right tabular-nums">{{ $angka }}</span>
        </li>
    @endforeach
</ul>
