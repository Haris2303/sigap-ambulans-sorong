@props(['judul' => null, 'peta' => false])
@php
    $pengguna = auth()->user();
    $menu = config('sigap.menu.'.$pengguna->role->value, []);
    $urlRingkasan = $pengguna->role === \App\Enums\Role::Admin ? route('admin.pesanan.ringkasan') : null;
@endphp
<x-layouts.dasar :judul="$judul" :peta="$peta">
    <div class="min-h-screen lg:flex">
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between gap-3 border-b border-line bg-surface px-4 lg:hidden">
            <a href="{{ route($pengguna->role->ruteBeranda()) }}" class="text-h3">SIGAP</a>
            <span class="min-w-0 flex-1 truncate text-label text-on-surface-muted">{{ $judul }}</span>
            <x-ui.tombol nada="hantu" ukuran="sm" ikon="menu" aria-label="Buka menu" data-menu-buka />
        </header>

        <aside data-menu-rel @if ($urlRingkasan) data-ringkasan-url="{{ $urlRingkasan }}" data-detik="{{ config('sigap.polling.admin_detik') }}" @endif class="fixed inset-y-0 left-0 z-40 hidden w-full flex-col border-r border-line bg-surface lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-rail lg:shrink-0">
            <div class="flex h-14 shrink-0 items-center justify-between px-4">
                <a href="{{ route($pengguna->role->ruteBeranda()) }}" class="text-h3">SIGAP Ambulans</a>
                <x-ui.tombol nada="hantu" ukuran="sm" ikon="x" aria-label="Tutup menu" class="lg:hidden" data-menu-tutup />
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Menu utama">
                @foreach ($menu as $item)
                    @php
                        $aktif = request()->routeIs($item['cocok']);
                        $jumlah = isset($item['lencana']) ? ($lencanaMenu[$item['lencana']] ?? 0) : 0;
                    @endphp
                    <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                        class="flex min-h-touch items-center gap-3 rounded-md px-3 text-label transition-colors {{ $aktif ? 'bg-primary-soft text-primary' : 'text-on-surface-muted hover:bg-canvas' }}">
                        <x-ui.ikon :nama="$item['ikon']" />
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (isset($item['lencana']))
                            <span data-lencana="{{ $item['lencana'] }}" class="tabular-nums text-warning @if ($jumlah < 1) hidden @endif">{{ $jumlah }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="shrink-0 border-t border-line p-3">
                <p class="truncate text-label">{{ $pengguna->name }}</p>
                <p class="mb-2 text-caption text-on-surface-muted">{{ $pengguna->role->label() }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.tombol tipe="submit" nada="sekunder" ukuran="sm" ikon="log-out" blok>Keluar</x-ui.tombol>
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
