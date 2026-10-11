@props(['judul', 'deskripsi' => null])
<section {{ $attributes->merge(['class' => 'mt-8']) }}>
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-h2">{{ $judul }}</h2>
            @if ($deskripsi)
                <p class="mt-1 max-w-prose text-body-sm text-on-surface-muted">{{ $deskripsi }}</p>
            @endif
        </div>
        @isset($aksi)
            <div class="flex flex-wrap gap-2">{{ $aksi }}</div>
        @endisset
    </div>
    {{ $slot }}
</section>
