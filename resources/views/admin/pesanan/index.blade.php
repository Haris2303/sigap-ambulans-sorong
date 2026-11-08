{{-- Daftar pesanan Dinkes arah B (docs/RETEMA_B.md 8.2, acuan kartu "Pesanan terbaru" B1-dasbor-1440.png). --}}
<x-layouts.app judul="Pesanan">
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Pesanan gawat dan yang paling lama menunggu tampil paling atas." />

    <x-ui.panel :flush="true" data-shot="tabel-pesanan">
        <div class="flex flex-wrap items-center gap-3 px-5 pt-5 lg:px-6 lg:pt-6">
            <x-ui.tabs label="Filter status pesanan" class="shrink-0">
                @foreach (\App\Enums\TabPesanan::cases() as $t)
                    <x-ui.tab :href="route('admin.pesanan.index', $queryTab + ['tab' => $t->value])" :aktif="$tab === $t">{{ $t->label() }}</x-ui.tab>
                @endforeach
            </x-ui.tabs>

            <form method="GET" action="{{ route('admin.pesanan.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab->value }}">
                @if (($filter['q'] ?? '') !== '')
                    <input type="hidden" name="q" value="{{ $filter['q'] }}">
                @endif
                <x-ui.pilihan nama="status" label="Status" :label-tersembunyi="true" :opsi="\App\Enums\PesananStatus::opsi()" :nilai="$filter['status'] ?? null" kosong="Semua status" class="w-40" />
                <x-ui.pilihan nama="jenis_layanan" label="Jenis layanan" :label-tersembunyi="true" :opsi="\App\Enums\JenisLayanan::opsi()" :nilai="$filter['jenis_layanan'] ?? null" kosong="Semua jenis" class="w-40" />
                <x-ui.isian nama="dari" label="Dari tanggal" :label-tersembunyi="true" tipe="date" :nilai="$filter['dari'] ?? null" class="w-36" />
                <x-ui.isian nama="sampai" label="Sampai tanggal" :label-tersembunyi="true" tipe="date" :nilai="$filter['sampai'] ?? null" class="w-36" />
                <x-ui.tombol nada="sekunder" tipe="submit" ikon="search" ikon-ukuran="18">Terapkan</x-ui.tombol>
                @if ($adaFilter)
                    <x-ui.tombol nada="hantu" :href="route('admin.pesanan.index', ['tab' => $tab->value])">Atur ulang</x-ui.tombol>
                @endif
            </form>

            <x-ui.cari :aksi="route('admin.pesanan.index')" label="Cari kode pesanan atau nama pasien" :nilai="$filter['q'] ?? ''" placeholder="Kode pesanan atau nama pasien" class="ml-auto max-w-xs lg:max-w-sm" />
        </div>

        <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
            @include('pesanan._baris', [
                'daftar' => $daftar,
                'rute' => 'admin.pesanan.show',
                'pesanKosong' => $adaFilter ? 'Tidak ada pesanan yang cocok dengan filter.' : ($tab === \App\Enums\TabPesanan::Semua ? 'Belum ada pesanan.' : 'Belum ada pesanan di tab ini.'),
            ])
        </div>
    </x-ui.panel>

    @if ($daftar->isNotEmpty())
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
