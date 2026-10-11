@props(['judul' => null, 'peta' => false])
<x-layouts.dasar :judul="$judul" :peta="$peta">
    <header class="bg-surface-container">
        <div class="mx-auto flex h-16 max-w-content items-center justify-between gap-4 px-4 md:px-8">
            <a href="{{ route('beranda') }}" class="text-title-l text-on-surface">SIGAP Ambulans</a>
            <nav class="flex items-center gap-2" aria-label="Menu publik">
                <x-ui.tombol nada="hantu" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
                @auth
                    <x-ui.tombol nada="sekunder" :href="route(auth()->user()->role->ruteBeranda())">Ke dasbor</x-ui.tombol>
                @else
                    <x-ui.tombol nada="sekunder" :href="route('login')">Masuk</x-ui.tombol>
                @endauth
            </nav>
        </div>
    </header>
    <main id="isi" class="mx-auto max-w-content px-4 py-8 md:px-8 md:py-12">
        <x-ui.flash />
        {{ $slot }}
    </main>
    <footer class="border-t border-outline-variant">
        <p class="mx-auto max-w-content px-4 py-4 text-body-s text-on-surface-variant md:px-8">Dinas Kesehatan Kota Sorong</p>
    </footer>
</x-layouts.dasar>
