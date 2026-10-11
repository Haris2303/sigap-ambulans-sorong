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
        <x-ui.tabel>
            <x-slot:kepala>
                <th>Faskes</th>
                <th>Ketersediaan</th>
            </x-slot:kepala>
            @foreach ($faskes as $f)
                <tr>
                    <td class="align-top">
                        <p class="text-label-l">{{ $f->nama }}</p>
                        <p class="text-body-s text-on-surface-variant">{{ $f->jenis->label() }}</p>
                        @if ($f->alamat)
                            <p class="text-body-s text-on-surface-variant">{{ $f->alamat }}</p>
                        @endif
                        @if ($f->telepon)
                            <p class="text-body-s tabular-nums text-on-surface-variant">{{ $f->telepon }}</p>
                        @endif
                    </td>
                    <td><x-ui.ketersediaan :faskes="$f" /></td>
                </tr>
            @endforeach
        </x-ui.tabel>
    @endif

    <x-ui.bagian judul="Peta Faskes">
        <x-peta.faskes :daftar="$faskes" />
    </x-ui.bagian>
</x-layouts.publik>
