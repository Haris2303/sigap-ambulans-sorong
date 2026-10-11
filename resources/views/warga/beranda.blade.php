<x-layouts.app judul="Beranda">
    <x-ui.judul-halaman judul="Beranda" :deskripsi="'Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:aksi>
            <x-ui.tombol :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <div class="flex flex-wrap gap-5">
        <x-ui.stat tone="primary" icon="clipboard-list" :value="$aktif->count()" label="Pesanan aktif" class="min-w-0 flex-[1_1_16rem]" />
        <x-ui.stat tone="success" icon="circle-check" :value="$jumlahSelesai" label="Pesanan selesai" :hint="'Dari '.$jumlahSemua.' pesanan'" class="min-w-0 flex-[1_1_16rem]" />
    </div>

    <x-ui.bagian judul="Pesanan aktif">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" :href="route('warga.pesanan.index')">Riwayat pesanan</x-ui.tombol>
        </x-slot:aksi>
        @if ($aktif->isEmpty())
            <x-ui.kosong pesan="Belum ada pesanan aktif.">
                <x-slot:aksi>
                    <x-ui.tombol nada="sekunder" :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
                </x-slot:aksi>
            </x-ui.kosong>
        @else
            @include('warga._tabel-pesanan', ['daftar' => $aktif])
        @endif
    </x-ui.bagian>
</x-layouts.app>
