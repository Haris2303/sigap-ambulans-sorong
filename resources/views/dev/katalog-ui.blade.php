<x-layouts.publik judul="Katalog gaya" :peta="true">
    <x-ui.judul-halaman judul="Katalog gaya" deskripsi="Semua komponen ui dengan data contoh. Hanya tersedia di environment local dan testing.">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ikon="arrow-left" :href="route('beranda')">Kembali</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.bagian judul="Tombol">
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.tombol>Utama</x-ui.tombol>
            <x-ui.tombol nada="sekunder">Sekunder</x-ui.tombol>
            <x-ui.tombol nada="bahaya">Bahaya</x-ui.tombol>
            <x-ui.tombol nada="hantu">Hantu</x-ui.tombol>
            <x-ui.tombol ukuran="sm">Kecil</x-ui.tombol>
            <x-ui.tombol ukuran="lg">Besar</x-ui.tombol>
            <x-ui.tombol ikon="crosshair">Dengan ikon</x-ui.tombol>
            <x-ui.tombol nada="bahaya" tipe="submit" konfirmasi="Pesanan akan dibatalkan dan tidak dapat dipulihkan." >Buka dialog</x-ui.tombol>
            <x-ui.tombol disabled>Nonaktif</x-ui.tombol>
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Lencana dan status pesanan">
        <div class="flex flex-wrap items-center gap-2">
            @foreach (\App\Support\Tampilan::NADA as $nada)
                <x-ui.lencana :nada="$nada">{{ ucfirst($nada) }}</x-ui.lencana>
            @endforeach
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            @foreach ($statusPesanan as $status)
                <x-ui.lencana-status :status="$status" />
            @endforeach
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Peringatan">
        <div class="space-y-3">
            <x-ui.peringatan nada="info">Pesanan baru menunggu verifikasi.</x-ui.peringatan>
            <x-ui.peringatan nada="sukses">Pesanan berhasil diverifikasi.</x-ui.peringatan>
            <x-ui.peringatan nada="peringatan">Data ketersediaan Faskes sudah lama.</x-ui.peringatan>
            <x-ui.peringatan nada="bahaya">Ambulans tidak tersedia. Pilih ambulans lain.</x-ui.peringatan>
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Ringkasan angka">
        <x-ui.ringkasan>
            <x-ui.ringkasan-sel label="Menunggu verifikasi" :nilai="3" nada="peringatan" keterangan="Perlu tindakan Dinkes" />
            <x-ui.ringkasan-sel label="Sedang berjalan" :nilai="5" nada="info" />
            <x-ui.ringkasan-sel label="Selesai hari ini" :nilai="12" nada="sukses" />
            <x-ui.ringkasan-sel label="Ambulans tersedia" :nilai="18" />
        </x-ui.ringkasan>
    </x-ui.bagian>

    <x-ui.bagian judul="Tabel dan paginasi">
        <x-ui.tabel>
            <x-slot:kepala>
                <th>Kode</th>
                <th>Status</th>
                <th class="hidden md:table-cell">Dibuat</th>
            </x-slot:kepala>
            @foreach (array_slice($statusPesanan, 0, 3) as $i => $status)
                <tr>
                    <td class="tabular-nums">SGP-{{ str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT) }}</td>
                    <td><x-ui.lencana-status :status="$status" /></td>
                    <td class="hidden md:table-cell"><x-ui.waktu nilai="2026-10-11 09:00:00" /></td>
                </tr>
            @endforeach
        </x-ui.tabel>
        <x-ui.paginasi :data="$halaman" />
    </x-ui.bagian>

    <x-ui.bagian judul="Keadaan kosong">
        <x-ui.kosong pesan="Belum ada pesanan.">
            <x-slot:aksi>
                <x-ui.tombol nada="sekunder" :href="route('beranda')">Buat pesanan</x-ui.tombol>
            </x-slot:aksi>
        </x-ui.kosong>
    </x-ui.bagian>

    <x-ui.bagian judul="Formulir">
        <x-ui.panel class="max-w-xl">
            <div class="space-y-5">
                <x-ui.isian nama="nama_pasien" label="Nama pasien" nilai="Budi Santoso" bantuan="Sesuai kartu identitas." :wajib="true" />
                <x-ui.isian nama="no_telepon_kontak" label="Nomor kontak" tipe="tel" galat="Nomor kontak wajib diisi." />
                <x-ui.pilihan nama="jenis" label="Jenis Faskes" :opsi="\App\Enums\JenisFaskes::opsi()" :nilai="\App\Enums\JenisFaskes::Puskesmas" kosong="Semua jenis" />
                <x-ui.area-teks nama="catatan" label="Catatan" nilai="Pasien sesak napas." />
                <x-ui.centang nama="aktif" label="Akun aktif" :nilai="true" />
                <div class="space-y-2">
                    @foreach (\App\Enums\JenisLayanan::cases() as $layanan)
                        <x-ui.opsi-baris nama="jenis_layanan" :nilai="$layanan->value" :judul="$layanan->label()" deskripsi="Contoh deskripsi layanan." :terpilih="$loop->first" />
                    @endforeach
                </div>
            </div>
            <x-slot:kaki>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
                <x-ui.tombol nada="hantu" :href="route('beranda')">Batal</x-ui.tombol>
            </x-slot:kaki>
        </x-ui.panel>
    </x-ui.bagian>

    <x-ui.bagian judul="Rincian dan linimasa">
        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.rincian>
                <x-ui.rincian-baris label="Pasien">Budi Santoso</x-ui.rincian-baris>
                <x-ui.rincian-baris label="Lokasi jemput">Jalan Basuki Rahmat Km 8</x-ui.rincian-baris>
                <x-ui.rincian-baris label="Dibuat"><x-ui.waktu nilai="2026-10-11 09:00:00" :relatif="true" /></x-ui.rincian-baris>
            </x-ui.rincian>
            <x-ui.linimasa :log="$log" />
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Ketersediaan Faskes">
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($faskes as $f)
                <div>
                    <h3 class="mb-2 text-h3">{{ $f->nama }}</h3>
                    <x-ui.ketersediaan :faskes="$f" />
                </div>
            @endforeach
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Grafik batang">
        <x-ui.grafik-batang :data="$grafik" class="max-w-xl" />
    </x-ui.bagian>

    <x-ui.bagian judul="Peta">
        <div class="grid gap-6 lg:grid-cols-2">
            <x-peta.pemilih />
            <x-peta.faskes :daftar="[]" />
        </div>
    </x-ui.bagian>
</x-layouts.publik>
