<x-layouts.publik judul="Katalog gaya" :peta="true">
    <x-ui.judul-halaman judul="Katalog gaya" deskripsi="Semua komponen ui dengan data contoh. Hanya tersedia di environment local dan testing.">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ikon="arrow-left" :href="route('beranda')">Kembali</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.bagian judul="Tombol">
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.tombol>Utama</x-ui.tombol>
            <x-ui.tombol nada="tonal">Tonal</x-ui.tombol>
            <x-ui.tombol nada="sekunder">Sekunder</x-ui.tombol>
            <x-ui.tombol nada="hantu">Hantu</x-ui.tombol>
            <x-ui.tombol nada="bahaya">Bahaya</x-ui.tombol>
            <x-ui.tombol ukuran="lg">Besar</x-ui.tombol>
            <x-ui.tombol ikon="crosshair">Dengan ikon</x-ui.tombol>
            <x-ui.tombol nada="hantu" ikon="menu" aria-label="Buka menu" />
            <x-ui.tombol nada="bahaya" tipe="submit" konfirmasi="Pesanan akan dibatalkan dan tidak dapat dipulihkan." >Buka dialog</x-ui.tombol>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <x-ui.tombol disabled>Utama nonaktif</x-ui.tombol>
            <x-ui.tombol nada="tonal" disabled>Tonal nonaktif</x-ui.tombol>
            <x-ui.tombol nada="sekunder" disabled>Sekunder nonaktif</x-ui.tombol>
            <x-ui.tombol nada="hantu" disabled>Hantu nonaktif</x-ui.tombol>
            <x-ui.tombol nada="bahaya" disabled>Bahaya nonaktif</x-ui.tombol>
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

    <x-ui.bagian judul="Snackbar">
        <x-ui.snackbar :tetap="false">Pesanan berhasil diverifikasi.</x-ui.snackbar>
    </x-ui.bagian>

    <x-ui.bagian judul="Kartu statistik">
        <div class="flex flex-wrap gap-5">
            <x-ui.stat :featured="true" icon="clock" :value="2" label="Menunggu verifikasi" hint="pesanan baru" class="min-w-64 flex-[1.5]">
                <x-slot:aksi>
                    <x-ui.tombol nada="on-primary" :href="route('beranda')">Verifikasi pesanan</x-ui.tombol>
                </x-slot:aksi>
            </x-ui.stat>
            <x-ui.stat tone="primary" icon="user-check" :value="1" label="Terverifikasi" hint="menunggu penugasan" class="min-w-56 flex-1" />
            <x-ui.stat tone="warning" icon="ambulance" :value="3" label="Sedang berjalan" hint="ditugaskan sampai di jalan" class="min-w-56 flex-1" />
            <x-ui.stat tone="success" icon="circle-check" :value="1" label="Selesai hari ini" hint="pesanan tuntas" :href="route('beranda')" class="min-w-56 flex-1" />
            <x-ui.stat tone="error" icon="triangle-alert" :value="0" label="Ditolak hari ini" class="min-w-56 flex-1" />
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Chip ikon, petak, tab, dan avatar">
        <div class="flex flex-wrap items-center gap-3">
            @foreach (['primary', 'warning', 'success', 'error'] as $tone)
                <x-ui.icon-chip :tone="$tone" icon="info" />
                <x-ui.icon-chip :tone="$tone" icon="info" size="lg" />
            @endforeach
            <x-ui.avatar nama="Admin Dinkes" />
            <span class="text-body-m">Admin Dinkes</span>
        </div>
        <x-ui.panel class="mt-5">
            <div class="grid grid-cols-2 gap-4">
                <x-ui.tile tone="primary" icon="ambulance" :value="19" label="Ambulans tersedia" />
                <x-ui.tile tone="error" icon="bed-double" :value="1" label="Bed IGD tersedia" />
                <x-ui.tile tone="warning" icon="bed-double" :value="1" label="Bed rawat inap tersedia" />
                <x-ui.tile tone="success" icon="stethoscope" :value="6" label="Dokter tersedia" />
            </div>
        </x-ui.panel>
        <x-ui.panel :flush="true" class="mt-5">
            <x-ui.tabs label="Contoh tab">
                <x-ui.tab :href="route('ui.katalog')" :aktif="true">Semua</x-ui.tab>
                <x-ui.tab :href="route('ui.katalog', ['tab' => 'perlu-tindakan'])">Perlu tindakan</x-ui.tab>
                <x-ui.tab :href="route('ui.katalog', ['tab' => 'berjalan'])">Berjalan</x-ui.tab>
            </x-ui.tabs>
        </x-ui.panel>
    </x-ui.bagian>

    <x-ui.bagian judul="Grafik">
        <div class="flex flex-wrap gap-5">
            <x-ui.panel class="min-w-0 flex-[2] basis-80">
                <h3 class="text-card-title">Pesanan 7 hari terakhir</h3>
                <p class="mb-2 text-body-m text-on-surface-variant">5 sampai 11 Okt 2026</p>
                <x-ui.chart.bar :labels="$labelBatang" :values="$nilaiBatang" :highlight="6" title="Grafik batang pesanan per hari" unit="pesanan" />
            </x-ui.panel>
            <x-ui.panel class="min-w-0 flex-1 basis-64">
                <h3 class="text-card-title">Status pesanan</h3>
                <p class="mb-2 text-body-m text-on-surface-variant">10 pesanan terbaru</p>
                <x-ui.chart.donut :segments="$segmenStatus" center-label="pesanan" title="Diagram cincin status 10 pesanan terbaru" />
            </x-ui.panel>
        </div>
        <x-ui.panel class="mt-5 max-w-sm">
            <x-ui.chart.donut :segments="[]" center-label="pesanan" title="Diagram cincin status pesanan" />
        </x-ui.panel>
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
                <x-ui.isian nama="kode_cadangan" label="Kode cadangan" nilai="Tidak dapat diubah" :tanpa-old="true" disabled />
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
                    <h3 class="mb-2 text-title-m">{{ $f->nama }}</h3>
                    <x-ui.ketersediaan :faskes="$f" />
                </div>
            @endforeach
        </div>
    </x-ui.bagian>

    <x-ui.bagian judul="Peta">
        <div class="grid gap-6 lg:grid-cols-2">
            <x-peta.pemilih />
            <x-peta.faskes :daftar="[]" />
        </div>
    </x-ui.bagian>
</x-layouts.publik>
