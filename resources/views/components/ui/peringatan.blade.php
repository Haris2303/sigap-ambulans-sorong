@props(['nada' => 'info'])
{{-- x-ui.note arah B (DESIGN.md 6.11): padding 12px 14px, ikon 18px, pasangan container, teks bobot 600 (gaya label-l). --}}
<div role="{{ $nada === 'bahaya' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'flex gap-3 rounded-md px-3.5 py-3 text-label-l '.\App\Support\Tampilan::kelasLembut($nada)]) }}>
    <x-ui.ikon :nama="\App\Support\Tampilan::ikonNada($nada)" :ukuran="18" />
    <div class="min-w-0 flex-1 self-center">{{ $slot }}</div>
</div>
