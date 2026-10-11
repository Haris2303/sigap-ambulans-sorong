@props(['nada' => 'info'])
<div role="{{ $nada === 'bahaya' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'flex gap-3 rounded-md px-4 py-3 text-body-m '.\App\Support\Tampilan::kelasLembut($nada)]) }}>
    <x-ui.ikon :nama="\App\Support\Tampilan::ikonNada($nada)" :ukuran="24" />
    <div class="min-w-0 flex-1 self-center">{{ $slot }}</div>
</div>
