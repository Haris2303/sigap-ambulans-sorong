<x-layouts.app judul="Ketersediaan">
    <x-ui.judul-halaman judul="Ketersediaan" :deskripsi="$faskes->nama.'. Data ini tampil di halaman Info Faskes dan dipakai Dinkes untuk penugasan.'" />

    <form method="POST" action="{{ route('petugas.ketersediaan.update') }}">
        @csrf
        @method('PUT')
        <x-ui.panel class="max-w-xl">
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.isian nama="bed_igd_tersedia" label="Bed IGD tersedia" tipe="number" min="0" :nilai="$faskes->bed_igd_tersedia" :wajib="true" />
                    <x-ui.isian nama="bed_igd_total" label="Total bed IGD" tipe="number" min="0" :nilai="$faskes->bed_igd_total" :wajib="true" />
                    <x-ui.isian nama="bed_rawat_inap_tersedia" label="Bed rawat inap tersedia" tipe="number" min="0" :nilai="$faskes->bed_rawat_inap_tersedia" :wajib="true" />
                    <x-ui.isian nama="bed_rawat_inap_total" label="Total bed rawat inap" tipe="number" min="0" :nilai="$faskes->bed_rawat_inap_total" :wajib="true" />
                    <x-ui.isian nama="dokter_umum_tersedia" label="Dokter umum tersedia" tipe="number" min="0" :nilai="$faskes->dokter_umum_tersedia" :wajib="true" />
                    <x-ui.isian nama="dokter_spesialis_tersedia" label="Dokter spesialis tersedia" tipe="number" min="0" :nilai="$faskes->dokter_spesialis_tersedia" :wajib="true" />
                </div>
                <x-ui.area-teks nama="dokter_spesialis_keterangan" label="Keterangan dokter spesialis" :nilai="$faskes->dokter_spesialis_keterangan" bantuan="Contoh: spesialis yang sedang bertugas." />
                <p class="text-caption text-on-surface-muted">
                    @if ($faskes->belumDiperbarui())
                        Belum pernah diperbarui.
                    @else
                        Terakhir diperbarui <x-ui.waktu :nilai="$faskes->ketersediaan_diperbarui_pada" :relatif="true" />.
                    @endif
                </p>
            </div>
            <x-slot:kaki>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
                <x-ui.tombol nada="hantu" :href="route('petugas.dashboard')">Batal</x-ui.tombol>
            </x-slot:kaki>
        </x-ui.panel>
    </form>
</x-layouts.app>
