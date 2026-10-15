<x-layouts.app judul="Dasbor">
    <x-ui.sapaan :nama="\Illuminate\Support\Str::before(auth()->user()->name, ' ')" :deskripsi="$faskes->nama.' ('.$faskes->jenis->label().')'" />

    {{-- Baris statistik ala dasbor Dinkes (docs/RETEMA_B.md 6.8/8.1): kartu navy hero pertama, sisanya putih.
         Semua angka sudah ada di halaman (dihitung dari koleksi yang sama, bukan query baru); tanpa sparkline
         karena riwayat harian tidak disediakan controller ini. --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
        <x-ui.stat :featured="true" icon="clipboard-list" :value="$pesananAktif->count()" label="Pesanan aktif"
            hint="menunggu atau berjalan" class="col-span-2 xl:col-span-1">
            <x-slot:aksi>
                <x-ui.tombol nada="on-secondary" :href="route('petugas.pesanan.index')">Lihat semua pesanan</x-ui.tombol>
            </x-slot:aksi>
        </x-ui.stat>
        <x-ui.stat tone="primary" icon="ambulance" :value="$faskes->ambulans_tersedia_count" label="Ambulans tersedia"
            :hint="'dari '.$ambulans->count().' ambulans'" />
        <x-ui.stat tone="success" icon="user-check" :value="$sopir->where('sedang_online', true)->count()" label="Sopir online"
            :hint="'dari '.$sopir->count().' sopir'" />
        <x-ui.stat tone="warning" icon="stethoscope" :value="$faskes->dokter_umum_tersedia + $faskes->dokter_spesialis_tersedia" label="Dokter tersedia"
            :hint="$faskes->dokter_umum_tersedia.' umum, '.$faskes->dokter_spesialis_tersedia.' spesialis'" />
    </div>

    <x-ui.panel class="mt-5">
        <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-title-l">Ketersediaan Faskes Anda</h2>
                <x-ui.pembaruan :faskes="$faskes" class="mt-1" />
            </div>
            <x-ui.tombol ukuran="sm" :href="route('petugas.ketersediaan.edit')">Perbarui ketersediaan</x-ui.tombol>
        </div>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
            <x-ui.ring-tile :tersedia="$faskes->ambulans_tersedia_count" :total="$ambulans->count()" label="Ambulans" />
            <x-ui.ring-tile :tersedia="$faskes->bed_igd_tersedia" :total="$faskes->bed_igd_total" label="Bed IGD" />
            <x-ui.ring-tile :tersedia="$faskes->bed_rawat_inap_tersedia" :total="$faskes->bed_rawat_inap_total" label="Bed rawat inap" />
            <x-ui.tile tone="primary" icon="stethoscope" :value="$faskes->dokter_umum_tersedia" label="Dokter umum" />
            <x-ui.tile tone="primary" icon="stethoscope" :value="$faskes->dokter_spesialis_tersedia" label="Dokter spesialis" />
        </div>
    </x-ui.panel>

    <x-ui.bagian judul="Penugasan masuk dan aktif" deskripsi="Pesanan yang memakai ambulans Faskes ini atau menuju Faskes ini.">
        <x-ui.panel :flush="true">
            <div class="px-5 pb-5 pt-5 lg:px-6 lg:pb-6 lg:pt-6">
                @include('pesanan._baris', [
                    'daftar' => $pesananAktif,
                    'rute' => 'petugas.pesanan.show',
                    'pesanKosong' => 'Belum ada pesanan aktif untuk Faskes ini.',
                ])
            </div>
        </x-ui.panel>
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
            <x-ui.panel :flush="true">
                <x-ui.tabel :bingkai="false" class="px-5 pb-5 pt-2 lg:px-6 lg:pb-6">
                    <x-slot:kepala>
                        <th>Plat nomor</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </x-slot:kepala>
                    @foreach ($ambulans as $a)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.icon-chip tone="neutral" icon="ambulance" />
                                    <p class="text-label-l tabular-nums">{{ $a->plat_nomor }}</p>
                                </div>
                            </td>
                            <td>{{ $a->jenis->label() }}</td>
                            <td><x-ui.lencana :nada="$a->status->nada()">{{ $a->status->label() }}</x-ui.lencana></td>
                            <td>
                                @if ($a->status !== \App\Enums\StatusAmbulans::Bertugas)
                                    <form method="POST" action="{{ route('petugas.ambulans.status', $a) }}" class="flex justify-end">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $a->status === \App\Enums\StatusAmbulans::Tersedia ? \App\Enums\StatusAmbulans::Servis->value : \App\Enums\StatusAmbulans::Tersedia->value }}">
                                        <x-ui.tombol nada="sekunder" ukuran="sm" tipe="submit">{{ $a->status === \App\Enums\StatusAmbulans::Tersedia ? 'Tandai servis' : 'Tandai tersedia' }}</x-ui.tombol>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.tabel>
            </x-ui.panel>
        @endif
    </x-ui.bagian>

    <x-ui.bagian judul="Sopir Faskes">
        @if ($sopir->isEmpty())
            <x-ui.kosong pesan="Faskes ini belum memiliki sopir." />
        @else
            <x-ui.panel :flush="true">
                <x-ui.tabel :bingkai="false" class="px-5 pb-5 pt-2 lg:px-6 lg:pb-6">
                    <x-slot:kepala>
                        <th>Nama</th>
                        <th>Online</th>
                        <th>Akun</th>
                    </x-slot:kepala>
                    @foreach ($sopir as $s)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :nama="$s->name" />
                                    <p class="text-label-l">{{ $s->name }}</p>
                                </div>
                            </td>
                            <td><x-ui.lencana :nada="$s->sedang_online ? 'sukses' : 'netral'">{{ $s->sedang_online ? 'Online' : 'Offline' }}</x-ui.lencana></td>
                            <td><x-ui.lencana :nada="$s->aktif ? 'sukses' : 'netral'">{{ $s->aktif ? 'Aktif' : 'Nonaktif' }}</x-ui.lencana></td>
                        </tr>
                    @endforeach
                </x-ui.tabel>
            </x-ui.panel>
        @endif
    </x-ui.bagian>
</x-layouts.app>
