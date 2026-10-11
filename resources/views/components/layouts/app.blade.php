@props(['judul' => null, 'peta' => false])
@php
    $pengguna = auth()->user();
    $menu = config('sigap.menu.'.$pengguna->role->value, []);
    $urlRingkasan = $pengguna->role === \App\Enums\Role::Admin ? route('admin.pesanan.ringkasan') : null;
@endphp
<x-layouts.dasar :judul="$judul" :peta="$peta">
    <div class="min-h-screen lg:flex">
        <header data-bilah-atas class="sticky top-0 z-30 flex h-16 items-center gap-2 bg-surface-container px-2 transition-shadow lg:hidden">
            <x-ui.tombol nada="hantu" ikon="menu" aria-label="Buka menu" data-menu-buka />
            <span class="min-w-0 flex-1 truncate text-title-l">{{ $judul }}</span>
        </header>

        <div data-menu-scrim class="fixed inset-0 z-30 hidden bg-scrim lg:hidden"></div>

        <aside data-menu-rel @if ($urlRingkasan) data-ringkasan-url="{{ $urlRingkasan }}" data-detik="{{ config('sigap.polling.admin_detik') }}" @endif class="fixed inset-y-0 left-0 z-40 hidden w-80 max-w-[calc(100%-3.5rem)] flex-col rounded-r-lg bg-surface-container-low shadow-level-1 lg:sticky lg:top-0 lg:z-auto lg:flex lg:h-screen lg:w-drawer lg:max-w-none lg:shrink-0 lg:rounded-none lg:shadow-none">
            <div class="flex h-16 shrink-0 items-center justify-between pl-6 pr-2">
                <a href="{{ route($pengguna->role->ruteBeranda()) }}" class="text-title-l">SIGAP Ambulans</a>
                <x-ui.tombol nada="hantu" ikon="x" aria-label="Tutup menu" class="lg:hidden" data-menu-tutup />
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2" aria-label="Menu utama">
                @foreach ($menu as $item)
                    @php
                        $aktif = request()->routeIs($item['cocok']);
                        $jumlah = isset($item['lencana']) ? ($lencanaMenu[$item['lencana']] ?? 0) : 0;
                    @endphp
                    <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                        class="lapisan-keadaan flex min-h-14 items-center gap-3 rounded-full px-4 text-label-l transition-colors {{ $aktif ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant' }}">
                        <x-ui.ikon :nama="$item['ikon']" />
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (isset($item['lencana']))
                            <span data-lencana="{{ $item['lencana'] }}" @if ($jumlah < 1) hidden @endif class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-error px-1 text-label-s tabular-nums text-on-error">{{ $jumlah }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="shrink-0 border-t border-outline-variant p-4">
                <p class="truncate text-label-l">{{ $pengguna->name }}</p>
                <p class="mb-3 text-body-s text-on-surface-variant">{{ $pengguna->role->label() }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.tombol tipe="submit" nada="sekunder" ikon="log-out" :blok="true">Keluar</x-ui.tombol>
                </form>
            </div>
        </aside>

        <main id="isi" class="min-w-0 flex-1">
            <div class="max-w-content p-4 md:p-8">
                <x-ui.flash />
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.dasar>
