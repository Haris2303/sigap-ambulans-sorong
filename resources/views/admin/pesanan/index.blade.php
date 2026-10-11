<x-layouts.app judul="Pesanan">
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Pesanan gawat dan yang paling lama menunggu tampil paling atas." />

    {{-- Filter di dalam kartu. Kata kunci dan tab dipertahankan lewat input tersembunyi; kata kunci diisi dari kolom cari di bilah atas. --}}
    <x-ui.panel class="mb-5">
        <form method="GET" action="{{ route('admin.pesanan.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="tab" value="{{ $tab->value }}">
            @if (($filter['q'] ?? '') !== '')
                <input type="hidden" name="q" value="{{ $filter['q'] }}">
            @endif
            <x-ui.pilihan nama="status" label="Status" :opsi="\App\Enums\PesananStatus::opsi()" :nilai="$filter['status'] ?? null" kosong="Semua status" />
            <x-ui.pilihan nama="jenis_layanan" label="Jenis layanan" :opsi="\App\Enums\JenisLayanan::opsi()" :nilai="$filter['jenis_layanan'] ?? null" kosong="Semua jenis" />
            <x-ui.isian nama="dari" label="Dari tanggal" tipe="date" :nilai="$filter['dari'] ?? null" />
            <x-ui.isian nama="sampai" label="Sampai tanggal" tipe="date" :nilai="$filter['sampai'] ?? null" />
            <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-4">
                <x-ui.tombol nada="sekunder" tipe="submit" ikon="search">Terapkan</x-ui.tombol>
                @if ($adaFilter)
                    <x-ui.tombol nada="hantu" :href="route('admin.pesanan.index', ['tab' => $tab->value])">Atur ulang</x-ui.tombol>
                @endif
            </div>
        </form>
    </x-ui.panel>

    <x-ui.panel :flush="true">
        <x-ui.tabs label="Filter status pesanan">
            @foreach (\App\Enums\TabPesanan::cases() as $t)
                <x-ui.tab :href="route('admin.pesanan.index', $queryTab + ['tab' => $t->value])" :aktif="$tab === $t">{{ $t->label() }}</x-ui.tab>
            @endforeach
        </x-ui.tabs>

        @if ($daftar->isEmpty())
            <p class="px-6 py-8 text-body-l text-on-surface-variant">
                {{ $adaFilter ? 'Tidak ada pesanan yang cocok dengan filter.' : ($tab === \App\Enums\TabPesanan::Semua ? 'Belum ada pesanan.' : 'Belum ada pesanan di tab ini.') }}
            </p>
        @else
            @include('admin._tabel-pesanan', ['daftar' => $daftar])
        @endif
    </x-ui.panel>

    @if ($daftar->isNotEmpty())
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
