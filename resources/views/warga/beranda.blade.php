<x-layouts.app judul="Beranda">
    {{-- Beranda Masyarakat arah B. Tidak dirinci RETEMA_B.md bagian 8 (bagian "Masyarakat dan Laporan" hanya menyebut
         Buat Pesanan dan kartu stat); disamakan dengan pola kepala dasbor Dinkes (sapaan jam, docs/RETEMA_B.md 5.1) dan
         kartu navy hero (docs/RETEMA_B.md 6.8/8.1), karena beranda ini memang dasbor pribadi Masyarakat (PETA_HALAMAN_RETEMA.md).
         Sapaan dihitung langsung di view (bukan pindah ke controller) supaya tidak mengubah lapisan di luar tampilan. --}}
    @php
        $jamSapaan = (int) now()->format('G');
        $sapaan = match (true) {
            $jamSapaan < 11 => 'pagi',
            $jamSapaan < 15 => 'siang',
            $jamSapaan < 18 => 'sore',
            default => 'malam',
        };
    @endphp
    <x-ui.judul-halaman :judul="'Selamat '.$sapaan.', '.\Illuminate\Support\Str::before(auth()->user()->name, ' ')" deskripsi="Ringkasan pesanan ambulans Anda." />

    <div class="relative overflow-hidden rounded-xl bg-secondary p-6 text-on-secondary shadow-card">
        <span aria-hidden="true" class="absolute -top-12.5 -right-12.5 size-42.5 rounded-full bg-primary/25"></span>
        <span aria-hidden="true" class="absolute -bottom-17.5 right-7.5 size-35 rounded-full bg-primary/15"></span>
        <div class="relative flex items-center gap-3">
            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-md bg-on-secondary/15">
                <x-ui.ikon nama="ambulance" :ukuran="22" class="text-on-secondary" />
            </span>
            <p class="text-title-m">Pesan ambulans</p>
        </div>
        <p class="relative mt-2.5 max-w-prose text-body-m text-primary-container">Butuh ambulans sekarang? Isi data pesanan dan Dinkes akan memverifikasi secepatnya.</p>
        <div class="relative mt-4">
            <x-ui.tombol nada="on-secondary" :href="route('warga.pesanan.buat')">Pesan ambulans</x-ui.tombol>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap gap-5">
        <x-ui.stat tone="primary" icon="clipboard-list" :value="$aktif->count()" label="Pesanan aktif" class="min-w-0 flex-[1_1_16rem]" />
        <x-ui.stat tone="success" icon="circle-check" :value="$jumlahSelesai" label="Pesanan selesai" :hint="'Dari '.$jumlahSemua.' pesanan'" class="min-w-0 flex-[1_1_16rem]" />
    </div>

    <x-ui.bagian judul="Pesanan aktif">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" :href="route('warga.pesanan.index')">Riwayat pesanan</x-ui.tombol>
        </x-slot:aksi>
        <x-ui.panel :flush="true">
            <div class="px-5 pb-5 pt-5 lg:px-6 lg:pb-6 lg:pt-6">
                @include('pesanan._baris', [
                    'daftar' => $aktif,
                    'rute' => 'warga.pesanan.show',
                    'pesanKosong' => 'Belum ada pesanan aktif.',
                    'kosongAksiHref' => route('warga.pesanan.buat'),
                    'kosongAksiLabel' => 'Pesan ambulans',
                ])
            </div>
        </x-ui.panel>
    </x-ui.bagian>
</x-layouts.app>
