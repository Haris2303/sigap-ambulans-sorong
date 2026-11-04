@props(['judul' => null, 'peta' => false])
<x-layouts.dasar :judul="$judul" :peta="$peta">
    <header class="mx-auto max-w-content px-4 lg:px-7 pt-5">
        <div class="flex h-15 items-center justify-between gap-4 {{ \App\Support\Tampilan::KARTU }} px-4">
            <a href="{{ route('beranda') }}" class="flex min-w-0 items-center gap-3 text-on-surface">
                <span class="inline-flex size-10.5 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">
                    <x-ui.ikon nama="plus" :ukuran="24" />
                </span>
                <span class="min-w-0 truncate text-brand">SIGAP</span>
            </a>
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
    <main id="isi" class="mx-auto max-w-content px-4 py-8 lg:px-7 md:py-12">
        <x-ui.flash />
        {{ $slot }}
    </main>
    <footer class="border-t border-outline-variant">
        <p class="mx-auto max-w-content px-4 py-4 text-body-s text-on-surface-variant lg:px-7">Dinas Kesehatan Kota Sorong</p>
    </footer>
</x-layouts.dasar>
