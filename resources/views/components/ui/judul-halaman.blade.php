@props(['judul', 'deskripsi' => null])
<header class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
    <div class="min-w-0">
        <h1 class="text-page-title">{{ $judul }}</h1>
        @if ($deskripsi)
            <p class="mt-1 max-w-prose text-body-l text-on-surface-variant">{{ $deskripsi }}</p>
        @endif
    </div>
    @isset($aksi)
        <div class="flex flex-wrap gap-2">{{ $aksi }}</div>
    @endisset
</header>
