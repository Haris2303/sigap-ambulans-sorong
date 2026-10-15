<x-layouts.publik judul="Info Faskes" :peta="true">
    <x-ui.judul-halaman judul="Info Faskes" deskripsi="Ketersediaan bed, dokter, dan ambulans di Fasilitas Kesehatan Kota Sorong. Data diperbarui oleh petugas masing-masing Faskes." />

    <form method="GET" action="{{ route('faskes.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="sm:w-64">
            <x-ui.pilihan nama="jenis" label="Jenis Faskes" :opsi="\App\Enums\JenisFaskes::opsi()" :nilai="$jenis" kosong="Semua jenis" />
        </div>
        <div class="flex gap-2">
            <x-ui.tombol nada="sekunder" tipe="submit">Cari</x-ui.tombol>
            @if ($jenis)
                <x-ui.tombol nada="hantu" :href="route('faskes.index')">Atur ulang</x-ui.tombol>
            @endif
        </div>
    </form>

    @if ($faskes->isEmpty())
        <x-ui.kosong pesan="Belum ada Faskes untuk jenis ini." />
    @else
        <x-ui.panel flush>
            <div class="divide-y divide-outline-variant">
                @foreach ($faskes as $f)
                    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start lg:px-6">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <x-ui.icon-chip tone="neutral" icon="building-2" />
                            <div class="min-w-0">
                                <p class="text-title-m">{{ $f->nama }}</p>
                                <p class="text-body-s text-on-surface-variant">{{ $f->jenis->label() }}</p>
                                @if ($f->alamat)
                                    <p class="text-body-s text-on-surface-variant">{{ $f->alamat }}</p>
                                @endif
                                @if ($f->telepon)
                                    <p class="text-body-s tabular-nums text-on-surface-variant">{{ $f->telepon }}</p>
                                @endif
                                <x-ui.pembaruan :faskes="$f" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex flex-wrap items-start gap-3 sm:shrink-0">
                            <x-ui.ring-tile :tersedia="$f->bed_igd_tersedia" :total="$f->bed_igd_total" label="Bed IGD" class="w-25" />
                            <x-ui.ring-tile :tersedia="$f->bed_rawat_inap_tersedia" :total="$f->bed_rawat_inap_total" label="Bed rawat inap" class="w-25" />
                            <div class="text-body-s text-on-surface-variant">
                                <p>Dokter umum <span class="tabular-nums text-on-surface">{{ $f->dokter_umum_tersedia }}</span></p>
                                <p>Dokter spesialis <span class="tabular-nums text-on-surface">{{ $f->dokter_spesialis_tersedia }}</span></p>
                                <p>Ambulans tersedia <span class="tabular-nums text-on-surface">{{ $f->ambulans_tersedia_count ?? '-' }}</span></p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>
    @endif

    <x-ui.bagian judul="Peta Faskes">
        <x-peta.faskes :daftar="$faskes" />
    </x-ui.bagian>
</x-layouts.publik>
