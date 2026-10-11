@props(['judul' => null, 'peta' => false])
{{-- Kerangka aplikasi (DESIGN.md Layout): laci navigasi putih 280px (lg ke atas; modal di bawah lg), bilah atas berupa kartu melayang
     (menu, pencarian, lonceng, avatar), isi di atas latar canvas dengan padding 16px (ponsel) atau 28px (lg). --}}
@php
    $pengguna = auth()->user();
    $peran = $pengguna->role->value;
    $menu = config('sigap.menu.'.$peran, []);
    $urlRingkasan = $pengguna->role === \App\Enums\Role::Admin ? route('admin.pesanan.ringkasan') : null;
    $aksiCari = config('sigap.bilah_atas.cari.'.$peran);
    $lonceng = config('sigap.bilah_atas.lonceng.'.$peran);
    $jumlahLonceng = $lonceng ? ($lencanaMenu[$lonceng['lencana']] ?? 0) : 0;
    $grupTerakhir = null;
@endphp
<x-layouts.dasar :judul="$judul" :peta="$peta">
    <div class="min-h-screen lg:flex">
        <div data-menu-scrim class="fixed inset-0 z-30 hidden bg-scrim lg:hidden"></div>

        <aside data-menu-rel @if ($urlRingkasan) data-ringkasan-url="{{ $urlRingkasan }}" data-detik="{{ config('sigap.polling.admin_detik') }}" @endif class="gulir-tipis fixed inset-y-0 left-0 z-40 hidden w-80 max-w-[calc(100%-3.5rem)] flex-col overflow-y-auto rounded-r-lg bg-surface-container-lowest shadow-level-1 lg:sticky lg:top-0 lg:z-auto lg:flex lg:h-screen lg:w-drawer lg:max-w-none lg:shrink-0 lg:rounded-none lg:border-r lg:border-surface-container-high lg:shadow-none">
            <div class="flex shrink-0 items-center justify-between gap-3 px-4 pb-5 pt-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">
                        <x-ui.ikon nama="plus" />
                    </span>
                    <a href="{{ route($pengguna->role->ruteBeranda()) }}" class="min-w-0 truncate text-title-l">SIGAP Ambulans</a>
                </div>
                <x-ui.tombol nada="hantu" ikon="x" aria-label="Tutup menu" class="lg:hidden" data-menu-tutup />
            </div>

            <nav class="flex-1 space-y-1 px-3 pb-2" aria-label="Menu utama">
                @foreach ($menu as $item)
                    @php
                        $aktif = request()->routeIs($item['cocok']);
                        $jumlah = isset($item['lencana']) ? ($lencanaMenu[$item['lencana']] ?? 0) : 0;
                        $grup = $item['grup'] ?? null;
                    @endphp
                    @if ($grup !== null && $grup !== $grupTerakhir)
                        <p class="px-4 pb-2 {{ $grupTerakhir === null ? 'pt-1' : 'pt-5' }} text-label-m text-on-surface-variant">{{ $grup }}</p>
                        @php($grupTerakhir = $grup)
                    @endif
                    <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                        class="lapisan-keadaan flex min-h-12 items-center gap-3.5 rounded-full px-4 text-label-l transition-colors {{ $aktif ? 'bg-primary text-on-primary shadow-nav-active' : 'text-on-surface-variant' }}">
                        <x-ui.ikon :nama="$item['ikon']" :ukuran="22" />
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (isset($item['lencana']))
                            <span data-lencana="{{ $item['lencana'] }}" @if ($jumlah < 1) hidden @endif class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1.5 text-label-m tabular-nums text-on-error">{{ $jumlah }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="mx-3 mb-3 mt-4 shrink-0">
                <div class="flex items-center gap-3 bg-surface-container-low rounded-lg p-3">
                    <x-ui.avatar :nama="$pengguna->name" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-label-l">{{ $pengguna->name }}</p>
                        <p class="text-body-s text-on-surface-variant">{{ $pengguna->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.tombol tipe="submit" nada="hantu" ikon="log-out" ikon-ukuran="20" aria-label="Keluar" />
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 bg-canvas px-4 lg:px-7 pb-5 pt-5">
                <div data-bilah-atas class="flex h-16 items-center gap-2 {{ \App\Support\Tampilan::KARTU }} px-3 lg:gap-4 lg:px-4">
                    <x-ui.tombol nada="hantu" ikon="menu" aria-label="Buka menu" class="lg:hidden" data-menu-buka />

                    @if ($aksiCari)
                        <x-ui.cari :aksi="route($aksiCari)" label="Cari kode pesanan atau nama pasien" placeholder="Cari kode pesanan atau nama pasien" class="lg:max-w-[460px]" />
                    @else
                        <div class="flex-1"></div>
                    @endif

                    @if ($lonceng)
                        <a href="{{ route($lonceng['route'], $lonceng['query'] ?? []) }}" data-lonceng="{{ $lonceng['lencana'] }}" data-label="{{ $lonceng['label'] }}" aria-label="{{ $lonceng['label'] }}, {{ $jumlahLonceng }}"
                            class="lapisan-keadaan target-sentuh relative ml-auto inline-flex size-10 shrink-0 items-center justify-center rounded-full text-on-surface-variant">
                            <x-ui.ikon nama="bell" />
                            <span data-lencana="{{ $lonceng['lencana'] }}" @if ($jumlahLonceng < 1) hidden @endif aria-hidden="true" class="absolute right-0 top-0 inline-flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-error px-1 text-label-s tabular-nums text-on-error">{{ $jumlahLonceng }}</span>
                        </a>
                    @endif

                    <div class="flex shrink-0 items-center gap-3 {{ $lonceng ? '' : 'ml-auto' }}">
                        <x-ui.avatar :nama="$pengguna->name" />
                        <div class="hidden min-w-0 lg:block">
                            <p class="max-w-48 truncate text-label-l">{{ $pengguna->name }}</p>
                            <p class="text-body-s text-on-surface-variant">{{ $pengguna->role->label() }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main id="isi" class="min-w-0 px-4 lg:px-7 pb-8">
                <div class="max-w-content">
                    <x-ui.flash />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-layouts.dasar>
