{{-- Isi bersama bilah samping desktop dan laci ponsel (RETEMA_B.md 5.1, 5.2): merek, menu per peran, blok pengguna.
     Variabel dari x-layouts.app: $pengguna, $menu, $lencanaMenu. Prop $laci menambahkan tombol tutup di kepala. --}}
@props(['pengguna', 'menu', 'lencanaMenu', 'laci' => false])
@php
    $grupTerakhir = null;
@endphp
<div class="flex shrink-0 items-center justify-between gap-3 pb-4">
    <a href="{{ route($pengguna->role->ruteBeranda()) }}" class="flex min-w-0 items-center gap-3">
        <span class="inline-flex size-10.5 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">
            <x-ui.ikon nama="plus" :ukuran="24" />
        </span>
        <span class="min-w-0 truncate text-brand">SIGAP</span>
    </a>
    @if ($laci)
        <x-ui.tombol nada="hantu" ikon="x" ikon-ukuran="21" aria-label="Tutup menu" data-menu-tutup />
    @endif
</div>

<nav class="min-h-0 flex-1 space-y-1 overflow-y-auto gulir-tipis" aria-label="Menu utama">
    @foreach ($menu as $item)
        @php
            $aktif = request()->routeIs($item['cocok']);
            $jumlah = isset($item['lencana']) ? ($lencanaMenu[$item['lencana']] ?? 0) : 0;
            $grup = $item['grup'] ?? null;
        @endphp
        @if ($grup !== null && $grup !== $grupTerakhir)
            <p class="px-3 pb-1.5 pt-3.5 text-label-m text-on-surface-variant">{{ $grup }}</p>
            @php($grupTerakhir = $grup)
        @endif
        <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
            class="lapisan-keadaan flex min-h-12 items-center gap-3 rounded-md px-3.5 text-label-l transition-colors {{ $aktif ? 'bg-primary-container text-primary' : 'text-on-surface-variant' }}">
            <x-ui.ikon :nama="$item['ikon']" :ukuran="21" />
            <span class="flex-1 truncate">{{ $item['label'] }}</span>
            @if (isset($item['lencana']))
                <span data-lencana="{{ $item['lencana'] }}" @if ($jumlah < 1) hidden @endif class="inline-flex h-5.5 min-w-5.5 items-center justify-center rounded-full bg-error px-1.5 text-chip text-on-error tabular-nums">{{ $jumlah }}</span><span class="sr-only"> menunggu verifikasi</span>
            @endif
        </a>
    @endforeach
</nav>

<div class="mt-4 shrink-0">
    <div class="flex items-center gap-3 rounded-lg bg-surface p-3">
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
