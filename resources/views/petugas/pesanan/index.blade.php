{{-- Daftar pesanan Faskes arah B (docs/RETEMA_B.md 8.2): pesanan yang dituju Faskes ini atau dipegang ambulansnya. --}}
<x-layouts.app judul="Pesanan">
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Pesanan yang dituju Faskes Anda atau dipegang ambulans Faskes Anda." />

    <x-ui.panel :flush="true">
        <div class="flex flex-wrap items-center gap-3 px-5 pt-5 lg:px-6 lg:pt-6">
            <x-ui.tabs label="Filter status pesanan" class="shrink-0">
                @foreach (\App\Enums\TabPesanan::cases() as $t)
                    <x-ui.tab :href="route('petugas.pesanan.index', $queryTab + ['tab' => $t->value])" :aktif="$tab === $t">{{ $t->label() }}</x-ui.tab>
                @endforeach
            </x-ui.tabs>

            <x-ui.cari :aksi="route('petugas.pesanan.index')" label="Cari kode pesanan atau nama pasien" :nilai="$filter['q'] ?? ''" placeholder="Kode pesanan atau nama pasien" class="ml-auto max-w-xs lg:max-w-sm" />
        </div>

        <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
            @include('pesanan._baris', [
                'daftar' => $daftar,
                'rute' => 'petugas.pesanan.show',
                'pesanKosong' => $tab === \App\Enums\TabPesanan::Semua ? 'Belum ada pesanan.' : 'Belum ada pesanan di tab ini.',
            ])
        </div>
    </x-ui.panel>

    @if ($daftar->isNotEmpty())
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
