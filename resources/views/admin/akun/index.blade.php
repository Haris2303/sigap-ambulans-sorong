<x-layouts.app :judul="$judulAkun">
    <x-ui.judul-halaman :judul="$judulAkun" :deskripsi="'Akun '.$judulAkun.' dikelola oleh Dinkes. Akun dinonaktifkan, bukan dihapus.'">
        <x-slot:aksi>
            <x-ui.tombol ikon="plus" :href="route($rute.'.create')">Tambah {{ $judulAkun }}</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <form method="GET" action="{{ route($rute.'.index') }}" class="mb-6 grid gap-3 sm:grid-cols-3">
        <x-ui.pilihan nama="faskes_id" label="Faskes" :opsi="$opsiFaskes" :nilai="$faskesId" kosong="Semua Faskes" />
        <x-ui.isian nama="cari" label="Nama atau email" :nilai="$cari" />
        <div class="flex items-end gap-2">
            <x-ui.tombol nada="sekunder" tipe="submit" ikon="search">Cari</x-ui.tombol>
            @if ($adaFilter)
                <x-ui.tombol nada="hantu" :href="route($rute.'.index')">Atur ulang</x-ui.tombol>
            @endif
        </div>
    </form>

    @if ($daftar->isEmpty())
        <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada akun yang cocok dengan filter.' : 'Belum ada akun '.$judulAkun.'.'" />
    @else
        <x-ui.tabel>
            <x-slot:kepala>
                <th>Nama</th>
                <th class="hidden md:table-cell">Email</th>
                <th class="hidden md:table-cell">Faskes</th>
                @if ($peran === \App\Enums\Role::Sopir)
                    <th class="hidden lg:table-cell">Online</th>
                @endif
                <th>Status</th>
                <th><span class="sr-only">Aksi</span></th>
            </x-slot:kepala>
            @foreach ($daftar as $akun)
                <tr>
                    <td><a href="{{ route($rute.'.edit', $akun) }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $akun->name }}</a></td>
                    <td class="hidden md:table-cell">{{ $akun->email }}</td>
                    <td class="hidden md:table-cell">{{ $akun->faskes?->nama ?? '-' }}</td>
                    @if ($peran === \App\Enums\Role::Sopir)
                        <td class="hidden lg:table-cell"><x-ui.lencana :nada="$akun->sedang_online ? 'sukses' : 'netral'">{{ $akun->sedang_online ? 'Online' : 'Offline' }}</x-ui.lencana></td>
                    @endif
                    <td><x-ui.lencana :nada="$akun->aktif ? 'sukses' : 'netral'">{{ $akun->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana></td>
                    <td>
                        <form method="POST" action="{{ route($rute.'.aktif', $akun) }}" class="flex justify-end">
                            @csrf
                            @method('PUT')
                            @if ($akun->aktif)
                                <x-ui.tombol nada="hantu" tipe="submit" konfirmasi="Akun tidak dapat masuk lagi sampai diaktifkan kembali." data-judul="Nonaktifkan {{ $akun->name }}?" data-aksi="Nonaktifkan">Nonaktifkan</x-ui.tombol>
                            @else
                                <x-ui.tombol nada="hantu" tipe="submit">Aktifkan</x-ui.tombol>
                            @endif
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-ui.tabel>
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
