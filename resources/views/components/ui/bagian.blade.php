@props(['judul', 'deskripsi' => null, 'jarak' => true])
<section {{ $attributes->merge(['class' => $jarak ? 'mt-8' : '']) }}>
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-title-l">{{ $judul }}</h2>
            @if ($deskripsi)
                <p class="mt-1 max-w-prose text-body-m text-on-surface-variant">{{ $deskripsi }}</p>
            @endif
        </div>
        @isset($aksi)
            <div class="flex flex-wrap gap-2">{{ $aksi }}</div>
        @endisset
    </div>
    {{ $slot }}
</section>
