<x-layouts.app judul="Ketersediaan">
    <x-ui.judul-halaman judul="Ketersediaan" :deskripsi="$faskes->nama.'. Data ini tampil di halaman Info Faskes dan dipakai Dinkes untuk penugasan.'" />

    <x-ui.panel class="max-w-3xl">
        <p class="mb-3 text-title-m">Ringkasan saat ini</p>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-ui.ring-tile :tersedia="$faskes->bed_igd_tersedia" :total="$faskes->bed_igd_total" label="Bed IGD" />
            <x-ui.ring-tile :tersedia="$faskes->bed_rawat_inap_tersedia" :total="$faskes->bed_rawat_inap_total" label="Bed rawat inap" />
            <x-ui.tile tone="primary" icon="stethoscope" :value="$faskes->dokter_umum_tersedia" label="Dokter umum" />
            <x-ui.tile tone="primary" icon="stethoscope" :value="$faskes->dokter_spesialis_tersedia" label="Dokter spesialis" />
        </div>

        @if ($faskes->belumDiperbarui())
            <x-ui.peringatan nada="peringatan" class="mt-5">Belum pernah diperbarui.</x-ui.peringatan>
        @elseif ($faskes->dataLama())
            <x-ui.peringatan nada="peringatan" class="mt-5">Data lama, terakhir diperbarui <x-ui.waktu :nilai="$faskes->ketersediaan_diperbarui_pada" :relatif="true" /></x-ui.peringatan>
        @else
            <p class="mt-5 text-body-s text-on-surface-variant">Terakhir diperbarui <x-ui.waktu :nilai="$faskes->ketersediaan_diperbarui_pada" :relatif="true" /></p>
        @endif

        <form method="POST" action="{{ route('petugas.ketersediaan.update') }}" id="form-ketersediaan-petugas">
            @csrf
            @method('PUT')
            <div class="mt-5 space-y-5 border-t border-outline-variant pt-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.isian nama="bed_igd_tersedia" label="Bed IGD tersedia" tipe="number" min="0" :nilai="$faskes->bed_igd_tersedia" :wajib="true" />
                    <x-ui.isian nama="bed_igd_total" label="Total bed IGD" tipe="number" min="0" :nilai="$faskes->bed_igd_total" :wajib="true" />
                    <x-ui.isian nama="bed_rawat_inap_tersedia" label="Bed rawat inap tersedia" tipe="number" min="0" :nilai="$faskes->bed_rawat_inap_tersedia" :wajib="true" />
                    <x-ui.isian nama="bed_rawat_inap_total" label="Total bed rawat inap" tipe="number" min="0" :nilai="$faskes->bed_rawat_inap_total" :wajib="true" />
                    <x-ui.isian nama="dokter_umum_tersedia" label="Dokter umum tersedia" tipe="number" min="0" :nilai="$faskes->dokter_umum_tersedia" :wajib="true" />
                    <x-ui.isian nama="dokter_spesialis_tersedia" label="Dokter spesialis tersedia" tipe="number" min="0" :nilai="$faskes->dokter_spesialis_tersedia" :wajib="true" />
                </div>
                <x-ui.area-teks nama="dokter_spesialis_keterangan" label="Keterangan dokter spesialis" :nilai="$faskes->dokter_spesialis_keterangan" bantuan="Contoh: spesialis yang sedang bertugas." />
            </div>

            <div class="mt-6 hidden items-center justify-end gap-2 border-t border-outline-variant pt-5 md:flex">
                <x-ui.tombol nada="hantu" :href="route('petugas.dashboard')">Batal</x-ui.tombol>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
            </div>
        </form>
    </x-ui.panel>

    <div class="sticky bottom-0 -mx-4 mt-6 border-t border-outline-variant bg-surface-container-lowest px-4 py-3 md:hidden">
        <x-ui.tombol tipe="submit" form="form-ketersediaan-petugas" ukuran="lg" class="w-full">Simpan</x-ui.tombol>
    </div>
</x-layouts.app>
