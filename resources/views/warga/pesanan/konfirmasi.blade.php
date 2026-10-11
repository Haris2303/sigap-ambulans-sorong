<x-layouts.app judul="Konfirmasi pesanan">
    <x-ui.judul-halaman judul="Konfirmasi pesanan" deskripsi="Periksa data berikut. Pesanan baru dikirim setelah Anda menekan Kirim pesanan." />

    <div class="max-w-xl">
        <x-ui.rincian>
            <x-ui.rincian-baris label="Jenis layanan">{{ $jenisLayanan->label() }}</x-ui.rincian-baris>
            <x-ui.rincian-baris label="Tingkat darurat"><x-ui.lencana :nada="$tingkatDarurat->nada()">{{ $tingkatDarurat->label() }}</x-ui.lencana></x-ui.rincian-baris>
            <x-ui.rincian-baris label="Lokasi jemput">{{ $draf['lokasi_jemput'] }}</x-ui.rincian-baris>
            <x-ui.rincian-baris label="Faskes tujuan">{{ $faskesTujuan?->nama ?? 'Belum ditentukan' }}</x-ui.rincian-baris>
            <x-ui.rincian-baris label="Nama pasien">{{ $draf['nama_pasien'] }}</x-ui.rincian-baris>
            <x-ui.rincian-baris label="Nomor kontak"><span class="tabular-nums">{{ $draf['no_telepon_kontak'] }}</span></x-ui.rincian-baris>
            <x-ui.rincian-baris label="Catatan">{{ $draf['catatan'] ?? '-' }}</x-ui.rincian-baris>
        </x-ui.rincian>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row">
            <form method="POST" action="{{ route('warga.pesanan.simpan') }}">
                @csrf
                <x-ui.tombol tipe="submit" ukuran="lg" :blok="true" memuat="Mengirim pesanan...">Kirim pesanan</x-ui.tombol>
            </form>
            <x-ui.tombol nada="sekunder" ukuran="lg" :href="route('warga.pesanan.buat')">Ubah</x-ui.tombol>
        </div>
    </div>
</x-layouts.app>
