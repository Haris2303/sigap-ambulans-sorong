<x-layouts.app judul="Penugasan" :peta="$penugasan !== null && $penugasan->lat_jemput !== null">
    <x-ui.judul-halaman judul="Penugasan" deskripsi="Aktifkan Online agar Dinkes dapat menugaskan Anda. Posisi GPS dikirim selama Online." />

    <div data-sopir data-url-penugasan="{{ route('sopir.penugasan') }}" data-url-lokasi="{{ route('sopir.lokasi') }}"
        data-detik="{{ config('sigap.polling.sopir_detik') }}" data-detik-lokasi="{{ config('sigap.polling.sopir_lokasi_detik') }}"
        data-online="{{ $sopir->sedang_online ? '1' : '0' }}" data-penugasan="{{ $kunciPenugasan }}">

        <x-ui.panel class="max-w-xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-label">Status Anda</p>
                    <x-ui.lencana :nada="$sopir->sedang_online ? 'sukses' : 'netral'">{{ $sopir->sedang_online ? 'Online' : 'Offline' }}</x-ui.lencana>
                    <p class="mt-1 text-caption text-on-surface-muted">{{ $sopir->faskes?->nama ?? 'Belum terhubung ke Faskes' }}</p>
                </div>
                <form method="POST" action="{{ route('sopir.online') }}">
                    @csrf
                    <input type="hidden" name="online" value="{{ $sopir->sedang_online ? '0' : '1' }}">
                    <x-ui.tombol :nada="$sopir->sedang_online ? 'sekunder' : 'utama'" tipe="submit" data-izin-notifikasi>{{ $sopir->sedang_online ? 'Offline' : 'Online' }}</x-ui.tombol>
                </form>
            </div>
        </x-ui.panel>

        @if ($penugasan === null)
            <x-ui.bagian judul="Penugasan aktif">
                <x-ui.kosong :pesan="$sopir->sedang_online ? 'Belum ada penugasan. Halaman ini diperbarui otomatis.' : 'Anda offline. Tekan Online untuk menerima penugasan.'" />
            </x-ui.bagian>
        @else
            <x-ui.bagian judul="Penugasan aktif">
                <x-slot:aksi>
                    <x-ui.lencana-status :status="$penugasan->status" />
                </x-slot:aksi>
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <x-ui.rincian>
                            <x-ui.rincian-baris label="Kode"><span class="tabular-nums">{{ $penugasan->kode }}</span></x-ui.rincian-baris>
                            <x-ui.rincian-baris label="Layanan">{{ $penugasan->jenis_layanan->label() }}
                                @if ($penugasan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)<x-ui.lencana nada="bahaya">{{ $penugasan->tingkat_darurat->label() }}</x-ui.lencana>@endif
                            </x-ui.rincian-baris>
                            <x-ui.rincian-baris label="Pasien">{{ $penugasan->nama_pasien }}</x-ui.rincian-baris>
                            <x-ui.rincian-baris label="Kontak"><a href="tel:{{ $penugasan->no_telepon_kontak }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-primary-hover">{{ $penugasan->no_telepon_kontak }}</a></x-ui.rincian-baris>
                            <x-ui.rincian-baris label="Lokasi jemput">{{ $penugasan->lokasi_jemput }}</x-ui.rincian-baris>
                            <x-ui.rincian-baris label="Faskes tujuan">{{ $penugasan->faskesTujuan?->nama ?? 'Belum ditentukan' }}</x-ui.rincian-baris>
                            <x-ui.rincian-baris label="Ambulans">{{ $penugasan->ambulans->plat_nomor }}</x-ui.rincian-baris>
                            @if ($penugasan->catatan)
                                <x-ui.rincian-baris label="Catatan">{{ $penugasan->catatan }}</x-ui.rincian-baris>
                            @endif
                        </x-ui.rincian>

                        @if ($penugasan->lat_jemput !== null && $penugasan->lng_jemput !== null)
                            <x-peta.pelacak :lat="$penugasan->lat_jemput" :lng="$penugasan->lng_jemput" :url-posisi="route('sopir.penugasan')" :detik="config('sigap.polling.sopir_detik')" />
                        @endif
                    </div>

                    <div>
                        <x-ui.panel judul="Tindakan">
                            @if ($penugasan->status === \App\Enums\PesananStatus::Ditugaskan)
                                <form method="POST" action="{{ route('sopir.pesanan.terima', $penugasan) }}">
                                    @csrf
                                    <x-ui.tombol tipe="submit" ukuran="lg" :blok="true">Terima</x-ui.tombol>
                                </form>
                                <form method="POST" action="{{ route('sopir.pesanan.tolak', $penugasan) }}" class="mt-6 space-y-3 border-t border-line pt-4">
                                    @csrf
                                    <x-ui.area-teks nama="alasan" label="Alasan menolak" :wajib="true" />
                                    <x-ui.tombol nada="bahaya" tipe="submit" :blok="true" konfirmasi="Penugasan dikembalikan ke Dinkes." data-judul="Tolak penugasan?" data-aksi="Tolak">Tolak</x-ui.tombol>
                                </form>
                            @elseif ($penugasan->status === \App\Enums\PesananStatus::Diterima)
                                <form method="POST" action="{{ route('sopir.pesanan.berangkat', $penugasan) }}">
                                    @csrf
                                    <x-ui.tombol tipe="submit" ukuran="lg" :blok="true">Mulai perjalanan</x-ui.tombol>
                                </form>
                            @else
                                <form method="POST" action="{{ route('sopir.pesanan.selesai', $penugasan) }}">
                                    @csrf
                                    <x-ui.tombol tipe="submit" ukuran="lg" :blok="true" konfirmasi="Pastikan pasien sudah diserahkan di Faskes tujuan." data-judul="Selesaikan pesanan?" data-aksi="Selesai" data-nada="utama">Selesai</x-ui.tombol>
                                </form>
                            @endif
                        </x-ui.panel>
                    </div>
                </div>
            </x-ui.bagian>
        @endif
    </div>

    <x-ui.bagian judul="Penugasan terakhir">
        @if ($riwayat->isEmpty())
            <x-ui.kosong pesan="Belum ada penugasan yang selesai." />
        @else
            <x-ui.tabel>
                <x-slot:kepala>
                    <th>Kode</th>
                    <th>Pasien</th>
                    <th>Selesai</th>
                </x-slot:kepala>
                @foreach ($riwayat as $p)
                    <tr>
                        <td class="tabular-nums">{{ $p->kode }}</td>
                        <td>{{ $p->nama_pasien }}</td>
                        <td><x-ui.waktu :nilai="$p->selesai_pada" /></td>
                    </tr>
                @endforeach
            </x-ui.tabel>
        @endif
    </x-ui.bagian>
</x-layouts.app>
