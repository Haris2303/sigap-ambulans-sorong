{{-- Detail pesanan Dinkes arah B (docs/RETEMA_B.md 8.2, acuan kartu "Detail pesanan dan linimasa" B2-komponen-1440.png). --}}
<x-layouts.app :judul="'Pesanan '.$pesanan->kode" :peta="$pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null">
    @php
        $dibuat = $pesanan->created_at->timezone(config('app.timezone'));
    @endphp
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Detail pesanan dan linimasa status.">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ikon="arrow-left" :href="route('admin.pesanan.index')">Daftar pesanan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    @if ($bolehTugaskan)
        <x-ui.panel judul="Tugaskan ambulans dan sopir" class="mb-5">
            @if (empty($opsiPenugasan['ambulans']) || empty($opsiPenugasan['sopir']))
                <x-ui.peringatan nada="peringatan">
                    @if (empty($opsiPenugasan['ambulans']))
                        Tidak ada ambulans yang tersedia.
                    @else
                        Tidak ada sopir online yang bebas.
                    @endif
                </x-ui.peringatan>
            @else
                <form method="POST" action="{{ route('admin.pesanan.tugaskan', $pesanan) }}" class="grid gap-4 sm:grid-cols-2 sm:items-end">
                    @csrf
                    @method('PUT')
                    <x-ui.pilihan nama="ambulans_id" label="Ambulans" :opsi="$opsiPenugasan['ambulans']" kosong="Pilih ambulans" :wajib="true" bantuan="Dikelompokkan per Faskes. Yang disarankan tampil lebih dulu." />
                    <x-ui.pilihan nama="sopir_id" label="Sopir" :opsi="$opsiPenugasan['sopir']" kosong="Pilih sopir" :wajib="true" bantuan="Sopir harus dari Faskes yang sama dengan ambulans." />
                    <x-ui.tombol tipe="submit" memuat="Menugaskan..." class="sm:col-span-2 sm:w-fit">Tugaskan</x-ui.tombol>
                </form>
            @endif
        </x-ui.panel>
    @endif

    <x-ui.panel data-shot="detail">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-title-xl tabular-nums">{{ $pesanan->kode }}</h2>
                    <x-ui.lencana-status :status="$pesanan->status" />
                    @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                        <x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana>
                    @endif
                    <x-ui.waktu-tunggu :pesanan="$pesanan" />
                </div>
                <p class="mt-1 text-body-s text-on-surface-variant">Dibuat {{ $dibuat->translatedFormat('j M Y') }}, {{ $dibuat->format('H.i') }} WIT</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($pesanan->status->final())
                    <p class="text-body-s text-on-surface-variant">Tidak ada aksi yang tersedia.</p>
                @endif

                @if ($bolehVerifikasi)
                    <form method="POST" action="{{ route('admin.pesanan.verifikasi', $pesanan) }}">
                        @csrf
                        @method('PUT')
                        <x-ui.tombol tipe="submit" memuat="Memverifikasi...">Verifikasi</x-ui.tombol>
                    </form>
                    <x-ui.tombol nada="bahaya" data-dialog-buka="dialog-tolak">Tolak</x-ui.tombol>
                @endif

                @if ($bolehLepas)
                    <x-ui.tombol nada="sekunder" data-dialog-buka="dialog-lepas">Ganti ambulans</x-ui.tombol>
                @endif

                @if ($bolehBatalkan)
                    <x-ui.tombol nada="bahaya" data-dialog-buka="dialog-batalkan">Batalkan</x-ui.tombol>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-8 md:grid-cols-2">
            <div class="min-w-0">
                <h3 class="mb-3 text-title-m">Data pesanan</h3>
                <x-ui.rincian :bingkai="false" class="-mx-6">
                    <x-ui.rincian-baris label="Pasien">{{ $pesanan->nama_pasien }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Nomor kontak"><a href="tel:{{ $pesanan->no_telepon_kontak }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->no_telepon_kontak }}</a></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Layanan">{{ $pesanan->jenis_layanan->label() }}@if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat), {{ \Illuminate\Support\Str::lower($pesanan->tingkat_darurat->label()) }}@endif</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Alamat jemput">{{ $pesanan->lokasi_jemput }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes tujuan">{{ $pesanan->faskesTujuan?->nama ?? 'Belum ditentukan oleh pemohon.' }}</x-ui.rincian-baris>
                    @if ($pesanan->ambulans)
                        <x-ui.rincian-baris label="Ambulans dan sopir">{{ $pesanan->ambulans->plat_nomor }}, {{ $pesanan->sopir?->name ?? '-' }}</x-ui.rincian-baris>
                    @endif
                    <x-ui.rincian-baris label="Pemohon">{{ $pesanan->pemohon->name }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Email pemohon">{{ $pesanan->pemohon->email }}</x-ui.rincian-baris>
                    @if ($pesanan->catatan)
                        <x-ui.rincian-baris label="Catatan">{{ $pesanan->catatan }}</x-ui.rincian-baris>
                    @endif
                    @if ($pesanan->diverifikasiOleh)
                        <x-ui.rincian-baris label="Diverifikasi oleh">{{ $pesanan->diverifikasiOleh->name }} ({{ $pesanan->diverifikasi_pada->timezone(config('app.timezone'))->translatedFormat('j M Y, H.i') }})</x-ui.rincian-baris>
                    @endif
                    @if ($pesanan->alasan_penolakan)
                        <x-ui.rincian-baris label="Alasan penolakan">{{ $pesanan->alasan_penolakan }}</x-ui.rincian-baris>
                    @endif
                    @if ($pesanan->alasan_pembatalan)
                        <x-ui.rincian-baris label="Alasan pembatalan">{{ $pesanan->alasan_pembatalan }}</x-ui.rincian-baris>
                    @endif
                </x-ui.rincian>

                @if ($pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null)
                    <div class="mt-4">
                        <x-peta.pelacak :lat="$pesanan->lat_jemput" :lng="$pesanan->lng_jemput" />
                    </div>
                @endif
            </div>

            <div class="min-w-0">
                <h3 class="mb-3 text-title-m">Linimasa</h3>
                <x-ui.linimasa :log="$pesanan->log" />
            </div>
        </div>
    </x-ui.panel>

    {{-- Dialog Tolak pesanan (docs/RETEMA_B.md 6.11, acuan "Tolak pesanan SGP-000041?" B2-komponen-1440.png). --}}
    @if ($bolehVerifikasi)
        <x-ui.dialog id="dialog-tolak" :judul="'Tolak pesanan '.$pesanan->kode.'?'">
            <p class="mt-2 text-body-m text-on-surface-variant">Pemesan akan diberi tahu. Alasan penolakan ditampilkan di linimasa.</p>
            <form method="POST" action="{{ route('admin.pesanan.tolak', $pesanan) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <x-ui.area-teks nama="alasan" label="Alasan penolakan" :wajib="true" />
                <div class="flex justify-end gap-2">
                    <x-ui.tombol nada="hantu" tipe="button" data-dialog-tutup>Batal</x-ui.tombol>
                    <x-ui.tombol nada="bahaya" tipe="submit" memuat="Menolak...">Tolak pesanan</x-ui.tombol>
                </div>
            </form>
        </x-ui.dialog>
    @endif

    {{-- Dialog Ganti ambulans: memakai aksi "Lepas penugasan" yang sudah ada (mengembalikan ke Terverifikasi supaya
         bisa ditugaskan ulang); tidak ada rute atau aturan alur baru, hanya label dan dialog arah B. --}}
    @if ($bolehLepas)
        <x-ui.dialog id="dialog-lepas" :judul="'Ganti ambulans dan sopir '.$pesanan->kode.'?'">
            <p class="mt-2 text-body-m text-on-surface-variant">Penugasan saat ini dilepas dan ambulans tersedia lagi. Pesanan kembali ke status Terverifikasi untuk ditugaskan ulang.</p>
            <form method="POST" action="{{ route('admin.pesanan.lepas', $pesanan) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <x-ui.area-teks nama="alasan" label="Alasan (opsional)" />
                <div class="flex justify-end gap-2">
                    <x-ui.tombol nada="hantu" tipe="button" data-dialog-tutup>Batal</x-ui.tombol>
                    <x-ui.tombol nada="bahaya" tipe="submit" memuat="Melepas...">Lepas penugasan</x-ui.tombol>
                </div>
            </form>
        </x-ui.dialog>
    @endif

    @if ($bolehBatalkan)
        <x-ui.dialog id="dialog-batalkan" :judul="'Batalkan pesanan '.$pesanan->kode.'?'">
            <p class="mt-2 text-body-m text-on-surface-variant">Pesanan akan dibatalkan dan ambulans dibebaskan bila sudah ditugaskan.</p>
            <form method="POST" action="{{ route('admin.pesanan.batalkan', $pesanan) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <x-ui.area-teks nama="alasan" label="Alasan pembatalan" :wajib="true" />
                <div class="flex justify-end gap-2">
                    <x-ui.tombol nada="hantu" tipe="button" data-dialog-tutup>Batal</x-ui.tombol>
                    <x-ui.tombol nada="bahaya" tipe="submit" memuat="Membatalkan...">Batalkan pesanan</x-ui.tombol>
                </div>
            </form>
        </x-ui.dialog>
    @endif
</x-layouts.app>
