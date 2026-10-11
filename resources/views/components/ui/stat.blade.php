@props(['tone' => 'primary', 'value', 'label', 'hint' => null, 'icon', 'featured' => false, 'href' => null])
{{-- Kartu statistik. Putih: chip ikon, angka, label, keterangan (tautan hanya pada label bila href). Utama (featured): latar primary, tone diabaikan, aksi lewat slot `aksi`. Angka diberi data-nilai agar dapat diperbarui. --}}
@if ($featured)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-primary px-6 py-5 text-on-primary shadow-card-raised']) }}>
        <div class="flex items-center gap-3">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-md bg-on-primary/18">
                <x-ui.ikon :nama="$icon" :ukuran="22" />
            </span>
            <p class="text-title-m">{{ $label }}</p>
        </div>
        <div class="mt-4 flex items-baseline gap-3">
            <p class="text-stat-hero tabular-nums" data-nilai>{{ $value }}</p>
            @if ($hint)
                <p class="text-body-m text-primary-container">{{ $hint }}</p>
            @endif
        </div>
        @isset($aksi)
            <div class="mt-4">{{ $aksi }}</div>
        @endisset
    </div>
@else
    <div {{ $attributes->merge(['class' => \App\Support\Tampilan::KARTU.' flex items-center gap-4 p-5']) }}>
        <x-ui.icon-chip :tone="$tone" :icon="$icon" size="lg" />
        <div class="min-w-0">
            <p class="text-stat-l tabular-nums" data-nilai>{{ $value }}</p>
            <p class="text-label-l">
                @if ($href)
                    <a href="{{ $href }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $label }}</a>
                @else
                    {{ $label }}
                @endif
            </p>
            @if ($hint)
                <p class="text-body-s text-on-surface-variant">{{ $hint }}</p>
            @endif
        </div>
    </div>
@endif
