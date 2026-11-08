@props(['nada' => 'netral', 'dot' => true, 'size' => 'md'])
{{-- Lencana/chip arah B (DESIGN.md 6.6): size="md" (bawaan) 26px, size="sm" 24px; dot=false untuk chip
     keterangan/delta tanpa titik (mis. perbandingan periode). nada menerima nada Indonesia lama dan tone arah B
     (lihat Tampilan::kelasLembut); untuk status pesanan pakai PesananStatus::tone() lewat x-ui.lencana-status. --}}
@php
    $tinggi = $size === 'sm' ? 'min-h-6' : 'min-h-6.5';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex '.$tinggi.' items-center gap-1.5 rounded-full px-2.75 py-0.5 text-chip '.\App\Support\Tampilan::kelasLembut($nada)]) }}>
    @if ($dot)
        <span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
