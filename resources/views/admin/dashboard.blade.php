<x-layouts.app judul="Dasbor">
    <x-ui.judul-halaman judul="Dasbor" deskripsi="Ringkasan pesanan dan ketersediaan Faskes. Angka diperbarui otomatis.">
        <x-slot:aksi>
            <x-ui.tombol :href="route('admin.pesanan.index', ['status' => \App\Enums\PesananStatus::Menunggu->value])">Verifikasi pesanan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.ringkasan>
        <x-ui.ringkasan-sel data-ringkasan="pesanan_menunggu" :label="\App\Enums\PesananStatus::Menunggu->label()" :nilai="$pesanan['pesanan_menunggu']" :nada="\App\Enums\PesananStatus::Menunggu->nada()" />
        <x-ui.ringkasan-sel data-ringkasan="pesanan_terverifikasi" :label="\App\Enums\PesananStatus::Terverifikasi->label()" :nilai="$pesanan['pesanan_terverifikasi']" :nada="\App\Enums\PesananStatus::Terverifikasi->nada()" keterangan="Menunggu penugasan" />
        <x-ui.ringkasan-sel data-ringkasan="pesanan_berjalan" label="Sedang berjalan" :nilai="$pesanan['pesanan_berjalan']" nada="info" />
        <x-ui.ringkasan-sel data-ringkasan="pesanan_selesai_hari_ini" label="Selesai hari ini" :nilai="$pesanan['pesanan_selesai_hari_ini']" nada="sukses" />
    </x-ui.ringkasan>

    <x-ui.bagian judul="Ketersediaan">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" :href="route('admin.ketersediaan.index')">Kelola ketersediaan</x-ui.tombol>
        </x-slot:aksi>
        <x-ui.ringkasan>
            <x-ui.ringkasan-sel label="Ambulans tersedia" :nilai="$ketersediaan['ambulans_tersedia']" />
            <x-ui.ringkasan-sel label="Bed IGD tersedia" :nilai="$ketersediaan['bed_igd_tersedia']" />
            <x-ui.ringkasan-sel label="Bed rawat inap tersedia" :nilai="$ketersediaan['bed_rawat_inap_tersedia']" />
            <x-ui.ringkasan-sel label="Dokter tersedia" :nilai="$ketersediaan['dokter_tersedia']" />
        </x-ui.ringkasan>
    </x-ui.bagian>

    <x-ui.bagian judul="Pesanan 7 hari terakhir">
        <x-ui.panel>
            <x-ui.grafik-batang :data="$grafik" />
        </x-ui.panel>
    </x-ui.bagian>

    <x-ui.bagian judul="Pesanan terbaru">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" :href="route('admin.pesanan.index')">Semua pesanan</x-ui.tombol>
        </x-slot:aksi>
        @if ($terbaru->isEmpty())
            <x-ui.kosong pesan="Belum ada pesanan." />
        @else
            @include('admin._tabel-pesanan', ['daftar' => $terbaru])
        @endif
    </x-ui.bagian>
</x-layouts.app>
