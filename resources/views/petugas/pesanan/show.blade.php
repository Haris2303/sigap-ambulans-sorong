<x-layouts.app :judul="'Pesanan '.$pesanan->kode" :peta="$pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null">
    <x-ui.judul-halaman :judul="'Pesanan '.$pesanan->kode">
        <x-slot:aksi>
            <x-ui.lencana-status :status="$pesanan->status" />
            <x-ui.tombol nada="sekunder" ukuran="sm" ikon="arrow-left" :href="route('petugas.dashboard')">Dasbor</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-8 lg:col-span-2">
            <x-ui.bagian judul="Rincian pesanan" :jarak="false">
                <x-ui.rincian>
                    <x-ui.rincian-baris label="Nama pasien">{{ $pesanan->nama_pasien }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Nomor kontak"><span class="tabular-nums">{{ $pesanan->no_telepon_kontak }}</span></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Jenis layanan">{{ $pesanan->jenis_layanan->label() }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Tingkat darurat"><x-ui.lencana :nada="$pesanan->tingkat_darurat->nada()">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Lokasi jemput">{{ $pesanan->lokasi_jemput }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes tujuan">{{ $pesanan->faskesTujuan?->nama ?? 'Belum ditentukan' }}</x-ui.rincian-baris>
                    @if ($pesanan->ambulans)
                        <x-ui.rincian-baris label="Ambulans">{{ $pesanan->ambulans->plat_nomor }} ({{ $pesanan->ambulans->faskes->nama }})</x-ui.rincian-baris>
                        <x-ui.rincian-baris label="Sopir">{{ $pesanan->sopir?->name ?? '-' }}</x-ui.rincian-baris>
                    @endif
                    @if ($pesanan->catatan)
                        <x-ui.rincian-baris label="Catatan">{{ $pesanan->catatan }}</x-ui.rincian-baris>
                    @endif
                </x-ui.rincian>
            </x-ui.bagian>

            @if ($pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null)
                <x-ui.bagian judul="Lokasi jemput" :jarak="false">
                    <x-peta.pelacak :lat="$pesanan->lat_jemput" :lng="$pesanan->lng_jemput" />
                </x-ui.bagian>
            @endif
        </div>

        <x-ui.bagian judul="Riwayat status" :jarak="false">
            <x-ui.linimasa :log="$pesanan->log" />
        </x-ui.bagian>
    </div>
</x-layouts.app>
