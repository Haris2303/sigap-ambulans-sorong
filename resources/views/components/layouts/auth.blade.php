@props(['judul' => null])
<x-layouts.dasar :judul="$judul">
    <main id="isi" class="mx-auto flex min-h-screen max-w-sm flex-col justify-center px-4 py-8">
        <a href="{{ route('beranda') }}" class="mb-6 text-h2 text-on-surface">SIGAP Ambulans</a>
        <x-ui.flash />
        {{ $slot }}
    </main>
</x-layouts.dasar>
