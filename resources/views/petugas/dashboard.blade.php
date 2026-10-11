<x-layouts.app judul="Dasbor">
    <x-ui.judul-halaman judul="Dasbor" :deskripsi="$faskes->nama.' ('.$faskes->jenis->label().')'">
        <x-slot:aksi>
            <x-ui.tombol :href="route('petugas.ketersediaan.edit')">Perbarui ketersediaan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    {{-- Ketersediaan sumber daya milik Faskes ini: kartu statistik (jumlah tersedia dari total). --}}
    <div class="flex flex-wrap gap-5">
        <x-ui.stat tone="primary" icon="ambulance" :value="$faskes->ambulans_tersedia_count" label="Ambulans tersedia"
            :hint="'dari '.$ambulans->count().' ambulans'" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat tone="error" icon="bed-double" :value="$faskes->bed_igd_tersedia" label="Bed IGD tersedia"
            :hint="'dari '.$faskes->bed_igd_total" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat tone="warning" icon="bed-double" :value="$faskes->bed_rawat_inap_tersedia" label="Bed rawat inap tersedia"
            :hint="'dari '.$faskes->bed_rawat_inap_total" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat tone="success" icon="stethoscope" :value="$faskes->dokter_umum_tersedia + $faskes->dokter_spesialis_tersedia" label="Dokter tersedia"
            :hint="$faskes->dokter_umum_tersedia.' umum, '.$faskes->dokter_spesialis_tersedia.' spesialis'" class="min-w-0 flex-[1_1_14rem]" />
    </div>
    <x-ui.pembaruan :faskes="$faskes" class="mt-3" />

    <x-ui.bagian judul="Penugasan masuk dan aktif" deskripsi="Pesanan yang memakai ambulans Faskes ini atau menuju Faskes ini.">
        @if ($pesananAktif->isEmpty())
            <x-ui.kosong pesan="Belum ada pesanan aktif untuk Faskes ini." />
        @else
            <x-ui.tabel>
                <x-slot:kepala>
                    <th>Kode</th>
                    <th>Pasien</th>
                    <th class="hidden md:table-cell">Peran Faskes</th>
                    <th>Status</th>
                </x-slot:kepala>
                @foreach ($pesananAktif as $p)
                    <tr>
                        <td class="tabular-nums">
                            <a href="{{ route('petugas.pesanan.show', $p) }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $p->kode }}</a>
                            @if ($p->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                                <div class="mt-1"><x-ui.lencana nada="bahaya">{{ $p->tingkat_darurat->label() }}</x-ui.lencana></div>
                            @endif
                        </td>
                        <td>{{ $p->nama_pasien }}</td>
                        <td class="hidden md:table-cell">{{ $p->ambulans?->faskes_id === $faskes->id ? 'Asal ambulans' : 'Tujuan' }}</td>
                        <td><x-ui.lencana-status :status="$p->status" /></td>
                    </tr>
                @endforeach
            </x-ui.tabel>
        @endif
    </x-ui.bagian>

    <x-ui.bagian judul="Ambulans Faskes" deskripsi="Tukar status antara Tersedia dan Servis. Status Bertugas diatur otomatis.">
        @if ($ambulans->isEmpty())
            <x-ui.kosong pesan="Faskes ini belum memiliki ambulans." />
        @else
            <div class="mb-5 grid gap-4 sm:grid-cols-3">
                @foreach ([[\App\Enums\StatusAmbulans::Tersedia, 'success', 'circle-check'], [\App\Enums\StatusAmbulans::Bertugas, 'warning', 'ambulance'], [\App\Enums\StatusAmbulans::Servis, 'error', 'settings']] as [$status, $nada, $ikon])
                    <x-ui.tile :tone="$nada" :icon="$ikon" :value="$ambulans->where('status', $status)->count()" :label="$status->label()" />
                @endforeach
            </div>
            <x-ui.tabel>
                <x-slot:kepala>
                    <th>Plat nomor</th>
                    <th class="hidden md:table-cell">Jenis</th>
                    <th>Status</th>
                    <th><span class="sr-only">Aksi</span></th>
                </x-slot:kepala>
                @foreach ($ambulans as $a)
                    <tr>
                        <td class="tabular-nums">{{ $a->plat_nomor }}</td>
                        <td class="hidden md:table-cell">{{ $a->jenis->label() }}</td>
                        <td><x-ui.lencana :nada="$a->status->nada()">{{ $a->status->label() }}</x-ui.lencana></td>
                        <td>
                            @if ($a->status !== \App\Enums\StatusAmbulans::Bertugas)
                                <form method="POST" action="{{ route('petugas.ambulans.status', $a) }}" class="flex justify-end">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $a->status === \App\Enums\StatusAmbulans::Tersedia ? \App\Enums\StatusAmbulans::Servis->value : \App\Enums\StatusAmbulans::Tersedia->value }}">
                                    <x-ui.tombol nada="sekunder" tipe="submit">{{ $a->status === \App\Enums\StatusAmbulans::Tersedia ? 'Tandai servis' : 'Tandai tersedia' }}</x-ui.tombol>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.tabel>
        @endif
    </x-ui.bagian>

    <x-ui.bagian judul="Sopir Faskes">
        @if ($sopir->isEmpty())
            <x-ui.kosong pesan="Faskes ini belum memiliki sopir." />
        @else
            <x-ui.tabel>
                <x-slot:kepala>
                    <th>Nama</th>
                    <th>Online</th>
                    <th>Akun</th>
                </x-slot:kepala>
                @foreach ($sopir as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td><x-ui.lencana :nada="$s->sedang_online ? 'sukses' : 'netral'">{{ $s->sedang_online ? 'Online' : 'Offline' }}</x-ui.lencana></td>
                        <td><x-ui.lencana :nada="$s->aktif ? 'sukses' : 'netral'">{{ $s->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana></td>
                    </tr>
                @endforeach
            </x-ui.tabel>
        @endif
    </x-ui.bagian>
</x-layouts.app>
