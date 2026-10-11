<x-layouts.app judul="Ambulans">
    <x-ui.judul-halaman judul="Ambulans" deskripsi="Armada ambulans per Faskes. Status Bertugas diatur otomatis oleh sistem saat penugasan.">
        <x-slot:aksi>
            <x-ui.tombol ikon="plus" :href="route('admin.ambulans.create')">Tambah Ambulans</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <form method="GET" action="{{ route('admin.ambulans.index') }}" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.pilihan nama="faskes_id" label="Faskes" :opsi="$opsiFaskes" :nilai="$faskesId" kosong="Semua Faskes" />
        <x-ui.pilihan nama="jenis" label="Jenis" :opsi="\App\Enums\JenisAmbulans::opsi()" :nilai="$jenis" kosong="Semua jenis" />
        <x-ui.pilihan nama="status" label="Status" :opsi="\App\Enums\StatusAmbulans::opsi()" :nilai="$status" kosong="Semua status" />
        <div class="flex items-end gap-2">
            <x-ui.tombol nada="sekunder" tipe="submit" ikon="search">Cari</x-ui.tombol>
            @if ($adaFilter)
                <x-ui.tombol nada="hantu" :href="route('admin.ambulans.index')">Atur ulang</x-ui.tombol>
            @endif
        </div>
    </form>

    @if ($daftar->isEmpty())
        <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada ambulans yang cocok dengan filter.' : 'Belum ada ambulans.'" />
    @else
        <x-ui.tabel>
            <x-slot:kepala>
                <th>Plat nomor</th>
                <th class="hidden md:table-cell">Faskes</th>
                <th class="hidden md:table-cell">Jenis</th>
                <th class="hidden lg:table-cell">Sopir tetap</th>
                <th>Status</th>
            </x-slot:kepala>
            @foreach ($daftar as $a)
                <tr>
                    <td class="tabular-nums"><a href="{{ route('admin.ambulans.edit', $a) }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $a->plat_nomor }}</a></td>
                    <td class="hidden md:table-cell">{{ $a->faskes->nama }}</td>
                    <td class="hidden md:table-cell">{{ $a->jenis->label() }}</td>
                    <td class="hidden lg:table-cell">{{ $a->sopirTetap?->name ?? '-' }}</td>
                    <td><x-ui.lencana :nada="$a->status->nada()">{{ $a->status->label() }}</x-ui.lencana></td>
                </tr>
            @endforeach
        </x-ui.tabel>
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
