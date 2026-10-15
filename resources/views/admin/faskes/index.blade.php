<x-layouts.app judul="Faskes">
    <x-ui.judul-halaman judul="Faskes" deskripsi="Fasilitas Kesehatan yang menjadi asal ambulans dan tujuan rujukan.">
        <x-slot:aksi>
            <x-ui.tombol ikon="plus" :href="route('admin.faskes.create')">Tambah Faskes</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.panel :flush="true">
        <div class="flex flex-wrap items-center gap-3 px-5 pt-5 lg:px-6 lg:pt-6">
            <form method="GET" action="{{ route('admin.faskes.index') }}" class="flex flex-wrap items-center gap-2">
                @if ($cari !== '')
                    <input type="hidden" name="cari" value="{{ $cari }}">
                @endif
                <x-ui.pilihan nama="jenis" label="Jenis" :label-tersembunyi="true" :opsi="\App\Enums\JenisFaskes::opsi()" :nilai="$jenis" kosong="Semua jenis" class="w-40" />
                <x-ui.tombol nada="sekunder" tipe="submit" ikon="search" ikon-ukuran="18">Terapkan</x-ui.tombol>
                @if ($adaFilter)
                    <x-ui.tombol nada="hantu" :href="route('admin.faskes.index')">Atur ulang</x-ui.tombol>
                @endif
            </form>

            <x-ui.cari :aksi="route('admin.faskes.index')" label="Cari nama Faskes" nama="cari" :nilai="$cari" placeholder="Cari nama Faskes" class="basis-full sm:ml-auto sm:basis-auto sm:max-w-xs lg:max-w-sm" />
        </div>

        <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
            @if ($daftar->isEmpty())
                <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada Faskes yang cocok dengan filter.' : 'Belum ada Faskes.'">
                    @unless ($adaFilter)
                        <x-slot:aksi>
                            <x-ui.tombol nada="sekunder" :href="route('admin.faskes.create')">Tambah Faskes</x-ui.tombol>
                        </x-slot:aksi>
                    @endunless
                </x-ui.kosong>
            @else
                <x-ui.tabel :bingkai="false" class="hidden md:block">
                    <x-slot:kepala>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Ambulans</th>
                        <th class="hidden lg:table-cell">Koordinat</th>
                        <th>Status</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </x-slot:kepala>
                    @foreach ($daftar as $f)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.icon-chip tone="neutral" icon="building-2" />
                                    <div class="min-w-0">
                                        <p class="truncate text-label-l">{{ $f->nama }}</p>
                                        <p class="truncate text-body-s text-on-surface-variant">{{ $f->alamat ?: 'Alamat belum diisi' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $f->jenis->label() }}</td>
                            <td class="tabular-nums">{{ $f->ambulans_count }}</td>
                            <td class="hidden lg:table-cell">
                                @if ($f->punyaKoordinat())
                                    <x-ui.lencana nada="sukses">Terisi</x-ui.lencana>
                                @else
                                    <x-ui.lencana nada="peringatan">Belum diisi</x-ui.lencana>
                                @endif
                            </td>
                            <td><x-ui.lencana :nada="$f->aktif ? 'sukses' : 'netral'">{{ $f->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana></td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.tombol nada="sekunder" ukuran="sm" :href="route('admin.faskes.edit', $f)">Ubah</x-ui.tombol>
                                    <form method="POST" action="{{ route('admin.faskes.aktif', $f) }}">
                                        @csrf
                                        @method('PUT')
                                        @if ($f->aktif)
                                            <x-ui.tombol nada="hantu" ukuran="sm" tipe="submit" konfirmasi="Faskes tidak lagi muncul untuk penugasan dan Info Faskes." data-judul="Nonaktifkan {{ $f->nama }}?" data-aksi="Nonaktifkan">Nonaktifkan</x-ui.tombol>
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
                    @foreach ($daftar as $f)
                        <li class="relative grid grid-cols-[40px_1fr_auto] items-center gap-3 border-t border-surface-container-highest py-3 first:border-t-0">
                            <x-ui.icon-chip tone="neutral" icon="building-2" />
                            <div class="min-w-0">
                                <p class="truncate text-label-l"><a href="{{ route('admin.faskes.edit', $f) }}" class="after:absolute after:inset-0">{{ $f->nama }}</a></p>
                                <p class="truncate text-body-s text-on-surface-variant">{{ $f->jenis->label() }}</p>
                            </div>
                            <x-ui.lencana :nada="$f->aktif ? 'sukses' : 'netral'" size="sm">{{ $f->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana>
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
