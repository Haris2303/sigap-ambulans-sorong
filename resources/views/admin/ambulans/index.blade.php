<x-layouts.app judul="Ambulans">
    <x-ui.judul-halaman judul="Ambulans" deskripsi="Armada ambulans per Faskes. Status Bertugas diatur otomatis oleh sistem saat penugasan.">
        <x-slot:aksi>
            <x-ui.tombol ikon="plus" :href="route('admin.ambulans.create')">Tambah Ambulans</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.panel :flush="true">
        <div class="px-5 pt-5 lg:px-6 lg:pt-6">
            <form method="GET" action="{{ route('admin.ambulans.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.pilihan nama="faskes_id" label="Faskes" :label-tersembunyi="true" :opsi="$opsiFaskes" :nilai="$faskesId" kosong="Semua Faskes" />
                <x-ui.pilihan nama="jenis" label="Jenis" :label-tersembunyi="true" :opsi="\App\Enums\JenisAmbulans::opsi()" :nilai="$jenis" kosong="Semua jenis" />
                <x-ui.pilihan nama="status" label="Status" :label-tersembunyi="true" :opsi="\App\Enums\StatusAmbulans::opsi()" :nilai="$status" kosong="Semua status" />
                <div class="flex items-center gap-2">
                    <x-ui.tombol nada="sekunder" tipe="submit" ikon="search" ikon-ukuran="18">Terapkan</x-ui.tombol>
                    @if ($adaFilter)
                        <x-ui.tombol nada="hantu" :href="route('admin.ambulans.index')">Atur ulang</x-ui.tombol>
                    @endif
                </div>
            </form>
        </div>

        <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
            @if ($daftar->isEmpty())
                <x-ui.kosong :pesan="$adaFilter ? 'Tidak ada ambulans yang cocok dengan filter.' : 'Belum ada ambulans.'">
                    @unless ($adaFilter)
                        <x-slot:aksi>
                            <x-ui.tombol nada="sekunder" :href="route('admin.ambulans.create')">Tambah Ambulans</x-ui.tombol>
                        </x-slot:aksi>
                    @endunless
                </x-ui.kosong>
            @else
                <x-ui.tabel :bingkai="false" class="hidden md:block">
                    <x-slot:kepala>
                        <th>Plat nomor</th>
                        <th>Faskes</th>
                        <th>Jenis</th>
                        <th class="hidden lg:table-cell">Sopir tetap</th>
                        <th>Status</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </x-slot:kepala>
                    @foreach ($daftar as $a)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.icon-chip tone="neutral" icon="ambulance" />
                                    <div class="min-w-0">
                                        <p class="truncate text-label-l tabular-nums">{{ $a->plat_nomor }}</p>
                                        <p class="truncate text-body-s text-on-surface-variant">{{ $a->faskes->nama }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $a->faskes->nama }}</td>
                            <td>{{ $a->jenis->label() }}</td>
                            <td class="hidden lg:table-cell">{{ $a->sopirTetap?->name ?? '-' }}</td>
                            <td><x-ui.lencana :nada="$a->status->nada()">{{ $a->status->label() }}</x-ui.lencana></td>
                            <td>
                                <div class="flex justify-end">
                                    <x-ui.tombol nada="sekunder" ukuran="sm" :href="route('admin.ambulans.edit', $a)">Ubah</x-ui.tombol>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.tabel>

                {{-- Daftar baris di bawah md (docs/RETEMA_B.md 6.9, sama pola dengan pesanan/_baris). --}}
                <ul role="list" class="md:hidden">
                    @foreach ($daftar as $a)
                        <li class="relative grid grid-cols-[40px_1fr_auto] items-center gap-3 border-t border-surface-container-highest py-3 first:border-t-0">
                            <x-ui.icon-chip tone="neutral" icon="ambulance" />
                            <div class="min-w-0">
                                <p class="truncate text-label-l tabular-nums"><a href="{{ route('admin.ambulans.edit', $a) }}" class="after:absolute after:inset-0">{{ $a->plat_nomor }}</a></p>
                                <p class="truncate text-body-s text-on-surface-variant">{{ $a->faskes->nama }}, {{ $a->jenis->label() }}</p>
                            </div>
                            <x-ui.lencana :nada="$a->status->nada()" size="sm">{{ $a->status->label() }}</x-ui.lencana>
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
