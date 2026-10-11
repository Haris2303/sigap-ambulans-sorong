<x-layouts.app judul="Dasbor">
    <x-ui.judul-halaman judul="Dasbor" :deskripsi="$faskes->nama.' ('.$faskes->jenis->label().')'">
        <x-slot:aksi>
            <x-ui.tombol :href="route('petugas.ketersediaan.edit')">Perbarui ketersediaan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.bagian judul="Ketersediaan saat ini" :jarak="false">
        <x-ui.panel>
            <x-ui.ketersediaan :faskes="$faskes" />
        </x-ui.panel>
    </x-ui.bagian>

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
                        <td class="tabular-nums"><a href="{{ route('petugas.pesanan.show', $p) }}" class="text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $p->kode }}</a></td>
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
