<x-layouts.app judul="Faskes">
    <x-ui.judul-halaman judul="Faskes" deskripsi="Fasilitas Kesehatan yang menjadi asal ambulans dan tujuan rujukan.">
        <x-slot:aksi>
            <x-ui.tombol ikon="plus" :href="route('admin.faskes.create')">Tambah Faskes</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <form method="GET" action="{{ route('admin.faskes.index') }}" class="mb-6 grid gap-3 sm:grid-cols-3">
        <x-ui.pilihan nama="jenis" label="Jenis" :opsi="\App\Enums\JenisFaskes::opsi()" :nilai="$jenis" kosong="Semua jenis" />
        <x-ui.isian nama="cari" label="Nama Faskes" :nilai="$cari" />
        <div class="flex items-end gap-2">
            <x-ui.tombol nada="sekunder" tipe="submit" ikon="search">Cari</x-ui.tombol>
            @if ($adaFilter)
                <x-ui.tombol nada="hantu" :href="route('admin.faskes.index')">Atur ulang</x-ui.tombol>
            @endif
        </div>
    </form>

    @if ($daftar->isEmpty())
        <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada Faskes yang cocok dengan filter.' : 'Belum ada Faskes.'" />
    @else
        <x-ui.tabel>
            <x-slot:kepala>
                <th>Nama</th>
                <th class="hidden md:table-cell">Jenis</th>
                <th class="hidden md:table-cell">Ambulans</th>
                <th class="hidden lg:table-cell">Koordinat</th>
                <th>Status</th>
                <th><span class="sr-only">Aksi</span></th>
            </x-slot:kepala>
            @foreach ($daftar as $f)
                <tr>
                    <td><a href="{{ route('admin.faskes.edit', $f) }}" class="text-primary underline underline-offset-2 hover:text-primary-hover">{{ $f->nama }}</a></td>
                    <td class="hidden md:table-cell">{{ $f->jenis->label() }}</td>
                    <td class="hidden tabular-nums md:table-cell">{{ $f->ambulans_count }}</td>
                    <td class="hidden lg:table-cell">
                        @if ($f->punyaKoordinat())
                            <x-ui.lencana nada="sukses">Terisi</x-ui.lencana>
                        @else
                            <x-ui.lencana nada="peringatan">Belum diisi</x-ui.lencana>
                        @endif
                    </td>
                    <td><x-ui.lencana :nada="$f->aktif ? 'sukses' : 'netral'">{{ $f->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana></td>
                    <td>
                        <form method="POST" action="{{ route('admin.faskes.aktif', $f) }}" class="flex justify-end">
                            @csrf
                            @method('PUT')
                            @if ($f->aktif)
                                <x-ui.tombol nada="hantu" ukuran="sm" tipe="submit" konfirmasi="Faskes tidak lagi muncul untuk penugasan dan Info Faskes." data-judul="Nonaktifkan {{ $f->nama }}?" data-aksi="Nonaktifkan">Nonaktifkan</x-ui.tombol>
                            @else
                                <x-ui.tombol nada="hantu" ukuran="sm" tipe="submit">Aktifkan</x-ui.tombol>
                            @endif
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-ui.tabel>
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
