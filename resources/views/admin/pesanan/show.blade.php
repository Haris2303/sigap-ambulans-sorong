<x-layouts.app :judul="'Pesanan '.$pesanan->kode" :peta="$pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null">
    <x-ui.judul-halaman :judul="'Pesanan '.$pesanan->kode">
        <x-slot:aksi>
            <x-ui.lencana-status :status="$pesanan->status" />
            <x-ui.tombol nada="sekunder" ikon="arrow-left" :href="route('admin.pesanan.index')">Daftar pesanan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    {{-- Urutan DOM sama dengan urutan visual di ponsel: pemohon, lokasi, aksi, lalu sisanya, sehingga tombol aksi tidak berada di dasar halaman.
         Di lg ke atas aksi menjadi kolom kanan yang melintasi semua baris (baris kelima fleksibel menampung tinggi lebihnya). --}}
    <div class="grid gap-5 lg:grid-cols-3 lg:grid-rows-[auto_auto_auto_auto_1fr]">
        <x-ui.bagian judul="Pemohon dan pasien" :jarak="false" class="lg:col-span-2 lg:col-start-1">
            <x-ui.rincian>
                <x-ui.rincian-baris label="Pemohon">{{ $pesanan->pemohon->name }}</x-ui.rincian-baris>
                <x-ui.rincian-baris label="Email pemohon">{{ $pesanan->pemohon->email }}</x-ui.rincian-baris>
                <x-ui.rincian-baris label="Nama pasien">{{ $pesanan->nama_pasien }}</x-ui.rincian-baris>
                <x-ui.rincian-baris label="Nomor kontak"><a href="tel:{{ $pesanan->no_telepon_kontak }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->no_telepon_kontak }}</a></x-ui.rincian-baris>
                <x-ui.rincian-baris label="Jenis layanan">{{ $pesanan->jenis_layanan->label() }}</x-ui.rincian-baris>
                <x-ui.rincian-baris label="Tingkat darurat"><x-ui.lencana :nada="$pesanan->tingkat_darurat->nada()">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana></x-ui.rincian-baris>
                @if ($pesanan->catatan)
                    <x-ui.rincian-baris label="Catatan">{{ $pesanan->catatan }}</x-ui.rincian-baris>
                @endif
                <x-ui.rincian-baris label="Dibuat"><x-ui.waktu :nilai="$pesanan->created_at" :relatif="true" /></x-ui.rincian-baris>
                @if ($pesanan->diverifikasiOleh)
                    <x-ui.rincian-baris label="Diverifikasi oleh">{{ $pesanan->diverifikasiOleh->name }} (<x-ui.waktu :nilai="$pesanan->diverifikasi_pada" />)</x-ui.rincian-baris>
                @endif
                @if ($pesanan->alasan_penolakan)
                    <x-ui.rincian-baris label="Alasan penolakan">{{ $pesanan->alasan_penolakan }}</x-ui.rincian-baris>
                @endif
                @if ($pesanan->alasan_pembatalan)
                    <x-ui.rincian-baris label="Alasan pembatalan">{{ $pesanan->alasan_pembatalan }}</x-ui.rincian-baris>
                @endif
            </x-ui.rincian>
        </x-ui.bagian>

        <x-ui.bagian judul="Lokasi jemput" :jarak="false" class="lg:col-span-2 lg:col-start-1">
            <p class="mb-3 text-body-l">{{ $pesanan->lokasi_jemput }}</p>
            @if ($pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null)
                <x-peta.pelacak :lat="$pesanan->lat_jemput" :lng="$pesanan->lng_jemput" />
            @else
                <p class="text-body-m text-on-surface-variant">Pemohon tidak menandai titik di peta.</p>
            @endif
        </x-ui.bagian>

        <div id="aksi" class="space-y-6 lg:col-start-3 lg:row-span-5 lg:row-start-1 lg:self-start">
            @if ($pesanan->status->final())
                <x-ui.panel judul="Aksi">
                    <p class="text-body-m text-on-surface-variant">Pesanan sudah berstatus {{ $pesanan->status->label() }}. Tidak ada aksi yang tersedia.</p>
                </x-ui.panel>
            @endif

            @if ($bolehVerifikasi)
                <x-ui.panel judul="Verifikasi">
                    <form method="POST" action="{{ route('admin.pesanan.verifikasi', $pesanan) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <p class="text-body-m text-on-surface-variant">Pastikan data pasien dan lokasi jemput sudah jelas.</p>
                        <x-ui.tombol tipe="submit" :blok="true">Verifikasi</x-ui.tombol>
                    </form>
                </x-ui.panel>
                <x-ui.panel judul="Tolak pesanan">
                    <form method="POST" action="{{ route('admin.pesanan.tolak', $pesanan) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <x-ui.area-teks nama="alasan" label="Alasan penolakan" :wajib="true" />
                        <x-ui.tombol nada="bahaya" tipe="submit" :blok="true" konfirmasi="Pesanan akan ditolak dan pemohon dapat melihat alasannya." data-judul="Tolak pesanan?" data-aksi="Tolak">Tolak</x-ui.tombol>
                    </form>
                </x-ui.panel>
            @endif

            @if ($bolehTugaskan)
                <x-ui.panel judul="Tugaskan ambulans dan sopir">
                    @if (empty($opsiPenugasan['ambulans']) || empty($opsiPenugasan['sopir']))
                        <x-ui.peringatan nada="peringatan">
                            @if (empty($opsiPenugasan['ambulans']))
                                Tidak ada ambulans yang tersedia.
                            @else
                                Tidak ada sopir online yang bebas.
                            @endif
                        </x-ui.peringatan>
                    @else
                        <form method="POST" action="{{ route('admin.pesanan.tugaskan', $pesanan) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <x-ui.pilihan nama="ambulans_id" label="Ambulans" :opsi="$opsiPenugasan['ambulans']" kosong="Pilih ambulans" :wajib="true" bantuan="Dikelompokkan per Faskes. Yang disarankan tampil lebih dulu." />
                            <x-ui.pilihan nama="sopir_id" label="Sopir" :opsi="$opsiPenugasan['sopir']" kosong="Pilih sopir" :wajib="true" bantuan="Sopir harus dari Faskes yang sama dengan ambulans." />
                            <x-ui.tombol tipe="submit" :blok="true">Tugaskan</x-ui.tombol>
                        </form>
                    @endif
                </x-ui.panel>
            @endif

            @if ($bolehLepas)
                <x-ui.panel judul="Lepas penugasan">
                    <form method="POST" action="{{ route('admin.pesanan.lepas', $pesanan) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <p class="text-body-m text-on-surface-variant">Pesanan kembali ke status Terverifikasi dan ambulans tersedia lagi.</p>
                        <x-ui.area-teks nama="alasan" label="Alasan (opsional)" />
                        <x-ui.tombol nada="bahaya" tipe="submit" :blok="true" konfirmasi="Penugasan ambulans dan sopir akan dilepas." data-judul="Lepas penugasan?" data-aksi="Lepas penugasan">Lepas penugasan</x-ui.tombol>
                    </form>
                </x-ui.panel>
            @endif

            @if ($bolehBatalkan)
                <x-ui.panel judul="Batalkan pesanan">
                    <form method="POST" action="{{ route('admin.pesanan.batalkan', $pesanan) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <x-ui.area-teks nama="alasan" label="Alasan pembatalan" :wajib="true" />
                        <x-ui.tombol nada="bahaya" tipe="submit" :blok="true" konfirmasi="Pesanan akan dibatalkan dan ambulans dibebaskan." data-judul="Batalkan pesanan?" data-aksi="Batalkan pesanan">Batalkan pesanan</x-ui.tombol>
                    </form>
                </x-ui.panel>
            @endif
        </div>

        <x-ui.bagian judul="Faskes tujuan" :jarak="false" class="lg:col-span-2 lg:col-start-1">
            @if ($pesanan->faskesTujuan)
                <p class="mb-2 text-label-l">{{ $pesanan->faskesTujuan->nama }} ({{ $pesanan->faskesTujuan->jenis->label() }})</p>
                <x-ui.ketersediaan :faskes="$pesanan->faskesTujuan" />
            @else
                <p class="text-body-m text-on-surface-variant">Belum ditentukan oleh pemohon.</p>
            @endif
        </x-ui.bagian>

        @if ($pesanan->ambulans)
            <x-ui.bagian judul="Ambulans dan sopir" :jarak="false" class="lg:col-span-2 lg:col-start-1">
                <x-ui.rincian>
                    <x-ui.rincian-baris label="Ambulans">{{ $pesanan->ambulans->plat_nomor }} ({{ $pesanan->ambulans->jenis->label() }})</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes asal">{{ $pesanan->ambulans->faskes->nama }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Sopir">{{ $pesanan->sopir?->name ?? '-' }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Ditugaskan"><x-ui.waktu :nilai="$pesanan->ditugaskan_pada" /></x-ui.rincian-baris>
                </x-ui.rincian>
            </x-ui.bagian>
        @endif

        <x-ui.bagian judul="Riwayat status" :jarak="false" class="lg:col-span-2 lg:col-start-1">
            <x-ui.linimasa :log="$pesanan->log" />
        </x-ui.bagian>
    </div>
</x-layouts.app>
