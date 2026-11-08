<x-layouts.app judul="Penugasan" :peta="$penugasan !== null && $penugasan->lat_jemput !== null">
    <x-ui.sapaan :nama="\Illuminate\Support\Str::before($sopir->name, ' ')" deskripsi="Aktifkan Online agar Dinkes dapat menugaskan Anda. Posisi GPS dikirim selama Online." />

    <div data-sopir data-url-penugasan="{{ route('sopir.penugasan') }}" data-url-lokasi="{{ route('sopir.lokasi') }}"
        data-detik="{{ config('sigap.polling.sopir_detik') }}" data-detik-lokasi="{{ config('sigap.polling.sopir_lokasi_detik') }}"
        data-online="{{ $sopir->sedang_online ? '1' : '0' }}" data-penugasan="{{ $kunciPenugasan }}">

        <x-ui.panel class="max-w-3xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-label-l">Status Anda</p>
                    <x-ui.lencana :nada="$sopir->sedang_online ? 'sukses' : 'netral'">{{ $sopir->sedang_online ? 'Online' : 'Offline' }}</x-ui.lencana>
                    <p class="mt-1 text-body-s text-on-surface-variant">{{ $sopir->faskes?->nama ?? 'Belum terhubung ke Faskes' }}</p>
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
            {{-- Penugasan aktif: satu kartu besar, satu kolom (mobile-first), tindakan di bawah rincian.
                 Kepala kartu sama pola dengan detail pesanan (docs/RETEMA_B.md 8.2): kode title-xl + lencana. --}}
            <x-ui.panel class="mt-5 max-w-3xl" data-penugasan-aktif>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 class="text-title-xl tabular-nums">{{ $penugasan->kode }}</h2>
                            <x-ui.lencana-status :status="$penugasan->status" />
                            @if ($penugasan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                                <x-ui.lencana nada="bahaya">{{ $penugasan->tingkat_darurat->label() }}</x-ui.lencana>
                            @endif
                        </div>
                        <p class="mt-1 text-body-s text-on-surface-variant">Tugas aktif</p>
                    </div>
                </div>

                <div class="mt-5">
                    <p class="text-label-l text-on-surface-variant">Lokasi jemput</p>
                    <p class="text-title-m">{{ $penugasan->lokasi_jemput }}</p>
                    <x-ui.navigasi class="mt-3" :lat="$penugasan->lat_jemput" :lng="$penugasan->lng_jemput" :alamat="$penugasan->lokasi_jemput" />
                </div>

                <x-ui.rincian :bingkai="false" class="mt-4 -mx-6">
                    <x-ui.rincian-baris label="Layanan">{{ $penugasan->jenis_layanan->label() }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Pasien">{{ $penugasan->nama_pasien }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Kontak"><a href="tel:{{ $penugasan->no_telepon_kontak }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $penugasan->no_telepon_kontak }}</a></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes tujuan">{{ $penugasan->faskesTujuan?->nama ?? 'Belum ditentukan' }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Ambulans">{{ $penugasan->ambulans->plat_nomor }}</x-ui.rincian-baris>
                    @if ($penugasan->catatan)
                        <x-ui.rincian-baris label="Catatan">{{ $penugasan->catatan }}</x-ui.rincian-baris>
                    @endif
                </x-ui.rincian>

                @if ($penugasan->lat_jemput !== null && $penugasan->lng_jemput !== null)
                    <div class="mt-4">
                        <x-peta.pelacak :lat="$penugasan->lat_jemput" :lng="$penugasan->lng_jemput" :url-posisi="route('sopir.penugasan')" :detik="config('sigap.polling.sopir_detik')" />
                    </div>
                @endif

                {{-- Tindakan: aksi utama berada di bilah lekat di dasar layar (terjangkau ibu jari); "Tolak" tersembunyi di pengungkap supaya tidak tersentuh keliru. --}}
                @if ($penugasan->status === \App\Enums\PesananStatus::Ditugaskan)
                    <x-ui.lipat judul="Tolak penugasan" class="mt-6" :terbuka="$errors->has('alasan')">
                        <form method="POST" action="{{ route('sopir.pesanan.tolak', $penugasan) }}" class="space-y-3">
                            @csrf
                            <x-ui.area-teks nama="alasan" label="Alasan menolak" :wajib="true" />
                            <x-ui.tombol nada="bahaya" tipe="submit" :blok="true" konfirmasi="Penugasan dikembalikan ke Dinkes." data-judul="Tolak penugasan?" data-aksi="Tolak">Tolak</x-ui.tombol>
                        </form>
                    </x-ui.lipat>
                    <x-ui.bilah-aksi>
                        <form method="POST" action="{{ route('sopir.pesanan.terima', $penugasan) }}">
                            @csrf
                            <x-ui.tombol tipe="submit" ukuran="lg" :blok="true">Terima</x-ui.tombol>
                        </form>
                    </x-ui.bilah-aksi>
                @elseif ($penugasan->status === \App\Enums\PesananStatus::Diterima)
                    <x-ui.bilah-aksi>
                        <form method="POST" action="{{ route('sopir.pesanan.berangkat', $penugasan) }}">
                            @csrf
                            <x-ui.tombol tipe="submit" ukuran="lg" :blok="true">Mulai perjalanan</x-ui.tombol>
                        </form>
                    </x-ui.bilah-aksi>
                @else
                    <x-ui.bilah-aksi>
                        <form method="POST" action="{{ route('sopir.pesanan.selesai', $penugasan) }}">
                            @csrf
                            <x-ui.tombol tipe="submit" ukuran="lg" :blok="true" konfirmasi="Pastikan pasien sudah diserahkan di Faskes tujuan." data-judul="Selesaikan pesanan?" data-aksi="Selesai" data-nada="utama">Selesai</x-ui.tombol>
                        </form>
                    </x-ui.bilah-aksi>
                @endif
            </x-ui.panel>
        @endif
    </div>

    <x-ui.bagian judul="Penugasan terakhir">
        <x-ui.panel :flush="true">
            <div class="px-5 pb-5 pt-5 lg:px-6 lg:pb-6 lg:pt-6">
                @include('pesanan._baris', [
                    'daftar' => $riwayat,
                    'pesanKosong' => 'Belum ada penugasan yang selesai.',
                    'kolomWaktu' => 'selesai_pada',
                    'labelWaktu' => 'Selesai',
                ])
            </div>
        </x-ui.panel>
    </x-ui.bagian>
</x-layouts.app>
