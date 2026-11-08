<x-layouts.app :judul="$judulAkun">
    <x-ui.judul-halaman :judul="$judulAkun" :deskripsi="'Akun '.$judulAkun.' dikelola oleh Dinkes. Akun dinonaktifkan, bukan dihapus.'">
        <x-slot:aksi>
            <x-ui.tombol ikon="plus" :href="route($rute.'.create')">Tambah {{ $judulAkun }}</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.panel :flush="true">
        <div class="flex flex-wrap items-center gap-3 px-5 pt-5 lg:px-6 lg:pt-6">
            <form method="GET" action="{{ route($rute.'.index') }}" class="flex flex-wrap items-center gap-2">
                @if ($cari !== '')
                    <input type="hidden" name="cari" value="{{ $cari }}">
                @endif
                <x-ui.pilihan nama="faskes_id" label="Faskes" :label-tersembunyi="true" :opsi="$opsiFaskes" :nilai="$faskesId" kosong="Semua Faskes" class="w-40" />
                <x-ui.tombol nada="sekunder" tipe="submit" ikon="search" ikon-ukuran="18">Terapkan</x-ui.tombol>
                @if ($adaFilter)
                    <x-ui.tombol nada="hantu" :href="route($rute.'.index')">Atur ulang</x-ui.tombol>
                @endif
            </form>

            <x-ui.cari :aksi="route($rute.'.index')" label="Cari nama atau email" nama="cari" :nilai="$cari" placeholder="Cari nama atau email" class="basis-full sm:ml-auto sm:basis-auto sm:max-w-xs lg:max-w-sm" />
        </div>

        <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
            @if ($daftar->isEmpty())
                <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada akun yang cocok dengan filter.' : 'Belum ada akun '.$judulAkun.'.'">
                    @unless ($adaFilter)
                        <x-slot:aksi>
                            <x-ui.tombol nada="sekunder" :href="route($rute.'.create')">Tambah {{ $judulAkun }}</x-ui.tombol>
                        </x-slot:aksi>
                    @endunless
                </x-ui.kosong>
            @else
                <x-ui.tabel :bingkai="false" class="hidden md:block">
                    <x-slot:kepala>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Faskes</th>
                        @if ($peran === \App\Enums\Role::Sopir)
                            <th class="hidden lg:table-cell">Online</th>
                        @endif
                        <th>Status</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </x-slot:kepala>
                    @foreach ($daftar as $akun)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :nama="$akun->name" />
                                    <div class="min-w-0">
                                        <p class="truncate text-label-l">{{ $akun->name }}</p>
                                        <p class="truncate text-body-s text-on-surface-variant">{{ $akun->faskes?->nama ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $akun->email }}</td>
                            <td>{{ $akun->faskes?->nama ?? '-' }}</td>
                            @if ($peran === \App\Enums\Role::Sopir)
                                <td class="hidden lg:table-cell"><x-ui.lencana :nada="$akun->sedang_online ? 'sukses' : 'netral'">{{ $akun->sedang_online ? 'Online' : 'Offline' }}</x-ui.lencana></td>
                            @endif
                            <td><x-ui.lencana :nada="$akun->aktif ? 'sukses' : 'netral'">{{ $akun->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana></td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.tombol nada="sekunder" ukuran="sm" :href="route($rute.'.edit', $akun)">Ubah</x-ui.tombol>
                                    <form method="POST" action="{{ route($rute.'.aktif', $akun) }}">
                                        @csrf
                                        @method('PUT')
                                        @if ($akun->aktif)
                                            <x-ui.tombol nada="hantu" ukuran="sm" tipe="submit" konfirmasi="Akun tidak dapat masuk lagi sampai diaktifkan kembali." data-judul="Nonaktifkan {{ $akun->name }}?" data-aksi="Nonaktifkan">Nonaktifkan</x-ui.tombol>
                                        @else
                                            <x-ui.tombol nada="hantu" ukuran="sm" tipe="submit">Aktifkan</x-ui.tombol>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.tabel>

                {{-- Daftar baris di bawah md (docs/RETEMA_B.md 6.9, sama pola dengan pesanan/_baris). --}}
                <ul role="list" class="md:hidden">
                    @foreach ($daftar as $akun)
                        <li class="relative grid grid-cols-[40px_1fr_auto] items-center gap-3 border-t border-surface-container-highest py-3 first:border-t-0">
                            <x-ui.avatar :nama="$akun->name" />
                            <div class="min-w-0">
                                <p class="truncate text-label-l"><a href="{{ route($rute.'.edit', $akun) }}" class="after:absolute after:inset-0">{{ $akun->name }}</a></p>
                                <p class="truncate text-body-s text-on-surface-variant">{{ $akun->faskes?->nama ?? '-' }}</p>
                            </div>
                            <x-ui.lencana :nada="$akun->aktif ? 'sukses' : 'netral'" size="sm">{{ $akun->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-ui.panel>

    @if ($daftar->isNotEmpty())
        <x-ui.paginasi :data="$daftar" />
    @endif
</x-layouts.app>
