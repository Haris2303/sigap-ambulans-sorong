@props([
    'tone' => 'primary',
    'value',
    'label',
    'hint' => null,
    'hintTone' => 'neutral',
    'icon',
    'featured' => false,
    'href' => null,
    'series' => null,
    'sparklineTone' => null,
])
{{-- Kartu statistik arah B (docs/RETEMA_B.md 6.8). Putih: baris atas chip ikon + sparkline opsional (disembunyikan di
     bawah sm), angka stat-l, label label-l, chip keterangan (x-ui.lencana tanpa titik). Navy (featured): kartu navy
     secondary dengan dua lingkaran dekoratif, angka stat-hero, keterangan text-primary-container, aksi lewat slot
     `aksi` (tombol nada on-secondary), penanda tambahan di samping keterangan lewat slot `penanda` (mis. lencana Gawat). --}}
@if ($featured)
    <div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl bg-secondary p-6 text-on-secondary shadow-card']) }}>
        <span aria-hidden="true" class="absolute -top-12.5 -right-12.5 size-42.5 rounded-full bg-primary/25"></span>
        <span aria-hidden="true" class="absolute -bottom-17.5 right-7.5 size-35 rounded-full bg-primary/15"></span>
        <div class="relative flex items-center gap-3">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-md bg-on-secondary/15">
                <x-ui.ikon :nama="$icon" :ukuran="22" class="text-on-secondary" />
            </span>
            <p class="text-title-m">{{ $label }}</p>
        </div>
        <div class="relative mt-2.5 flex flex-wrap items-baseline gap-x-3 gap-y-2">
            <p class="text-stat-hero tabular-nums" data-nilai>{{ $value }}</p>
            @if ($hint)
                <p class="text-body-m text-primary-container">{{ $hint }}</p>
            @endif
            @isset($penanda)
                {{ $penanda }}
            @endisset
        </div>
        @isset($aksi)
            <div class="relative mt-3">{{ $aksi }}</div>
        @endisset
    </div>
@else
    <div {{ $attributes->merge(['class' => \App\Support\Tampilan::KARTU.' p-5 lg:p-6']) }}>
        <div class="flex items-start justify-between gap-3">
            <x-ui.icon-chip :tone="$tone" :icon="$icon" size="lg" />
            @if ($series)
                <span aria-hidden="true" class="hidden shrink-0 sm:inline-flex">{!! \App\Support\Chart\Sparkline::buat($series, $sparklineTone ?? $tone) !!}</span>
            @endif
        </div>
        <p class="mt-3.5 text-stat-l tabular-nums" data-nilai>{{ $value }}</p>
        <p class="text-label-l {{ $hint ? 'mb-2' : '' }}">
            @if ($href)
                <a href="{{ $href }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $label }}</a>
            @else
                {{ $label }}
            @endif
        </p>
        @if ($hint)
            <x-ui.lencana :nada="$hintTone" :dot="false" size="sm">{{ $hint }}</x-ui.lencana>
        @endif
    </div>
@endif
