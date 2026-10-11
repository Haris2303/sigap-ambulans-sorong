@props(['nada' => 'info'])
<div role="{{ $nada === 'bahaya' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'flex gap-3 rounded-md border border-line px-4 py-3 text-body-sm '.\App\Support\Tampilan::kelasLembut($nada)]) }}>
    <x-ui.ikon :nama="\App\Support\Tampilan::ikonNada($nada)" :ukuran="20" />
    <div class="min-w-0 flex-1">{{ $slot }}</div>
</div>
