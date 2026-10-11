@props(['judul', 'deskripsi' => null])
<header class="mb-6 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
    <div class="min-w-0">
        <h1 class="text-h1">{{ $judul }}</h1>
        @if ($deskripsi)
            <p class="mt-1 max-w-prose text-body-sm text-on-surface-muted">{{ $deskripsi }}</p>
        @endif
    </div>
    @isset($aksi)
        <div class="flex flex-wrap gap-2">{{ $aksi }}</div>
    @endisset
</header>
