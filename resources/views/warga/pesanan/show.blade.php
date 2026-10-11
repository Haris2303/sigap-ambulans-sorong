<x-layouts.app :judul="'Pesanan '.$pesanan->kode" :peta="$tampilkanPeta">
    <x-ui.judul-halaman :judul="'Pesanan '.$pesanan->kode">
        <x-slot:aksi>
            <x-ui.lencana-status :status="$pesanan->status" />
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <div class="grid gap-5 lg:grid-cols-3" data-lacak data-status="{{ $pesanan->status->value }}"
        data-url="{{ route('warga.pesanan.status', $pesanan) }}" data-detik="{{ config('sigap.polling.warga_detik') }}">
        <div class="space-y-5 lg:col-span-2">
            <x-ui.bagian judul="Rincian pesanan" :jarak="false">
                <x-ui.rincian>
                    <x-ui.rincian-baris label="Jenis layanan">{{ $pesanan->jenis_layanan->label() }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Tingkat darurat"><x-ui.lencana :nada="$pesanan->tingkat_darurat->nada()">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Nama pasien">{{ $pesanan->nama_pasien }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Nomor kontak"><span class="tabular-nums">{{ $pesanan->no_telepon_kontak }}</span></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Lokasi jemput">{{ $pesanan->lokasi_jemput }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes tujuan">{{ $pesanan->faskesTujuan?->nama ?? 'Belum ditentukan' }}</x-ui.rincian-baris>
                    @if ($pesanan->catatan)
                        <x-ui.rincian-baris label="Catatan">{{ $pesanan->catatan }}</x-ui.rincian-baris>
                    @endif
                    @if ($pesanan->alasan_penolakan)
                        <x-ui.rincian-baris label="Alasan penolakan">{{ $pesanan->alasan_penolakan }}</x-ui.rincian-baris>
                    @endif
                    @if ($pesanan->alasan_pembatalan)
                        <x-ui.rincian-baris label="Alasan pembatalan">{{ $pesanan->alasan_pembatalan }}</x-ui.rincian-baris>
                    @endif
                </x-ui.rincian>
            </x-ui.bagian>

            @if ($tampilkanSopir)
                <x-ui.bagian judul="Ambulans dan sopir" :jarak="false">
                    <x-ui.rincian>
                        <x-ui.rincian-baris label="Ambulans">{{ $pesanan->ambulans->plat_nomor }} ({{ $pesanan->ambulans->jenis->label() }})</x-ui.rincian-baris>
                        <x-ui.rincian-baris label="Faskes asal">{{ $pesanan->ambulans->faskes->nama }}</x-ui.rincian-baris>
                        <x-ui.rincian-baris label="Sopir">{{ $pesanan->sopir->name }}</x-ui.rincian-baris>
                        @if ($pesanan->sopir->no_telepon)
                            <x-ui.rincian-baris label="Telepon sopir"><a href="tel:{{ $pesanan->sopir->no_telepon }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->sopir->no_telepon }}</a></x-ui.rincian-baris>
                        @endif
                    </x-ui.rincian>
                </x-ui.bagian>
            @endif

            @if ($tampilkanPeta)
                <x-ui.bagian judul="Posisi ambulans" :jarak="false">
                    <x-peta.pelacak :lat="$pesanan->lat_jemput" :lng="$pesanan->lng_jemput" :url-posisi="route('warga.pesanan.status', $pesanan)" />
                </x-ui.bagian>
            @endif

            <x-ui.bagian judul="Riwayat status" :jarak="false">
                <x-ui.linimasa :log="$pesanan->log" />
            </x-ui.bagian>
        </div>

        <div>
            @if ($bolehBatal)
                <x-ui.panel judul="Batalkan pesanan">
                    <form method="POST" action="{{ route('warga.pesanan.batalkan', $pesanan) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <p class="text-body-m text-on-surface-variant">Pesanan hanya dapat dibatalkan sebelum ambulans ditugaskan.</p>
                        <x-ui.area-teks nama="alasan" label="Alasan (opsional)" />
                        <x-ui.tombol nada="bahaya" tipe="submit" :blok="true" konfirmasi="Pesanan ini akan dibatalkan dan tidak dapat dipulihkan." data-judul="Batalkan pesanan?" data-aksi="Batalkan pesanan">Batalkan pesanan</x-ui.tombol>
                    </form>
                </x-ui.panel>
            @endif
        </div>
    </div>

    @unless ($pesanan->status->final())
        @push('skrip')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const lacak = document.querySelector('[data-lacak]');
                    window.Sigap.polling(lacak.dataset.url, Number(lacak.dataset.detik), (hasil) => {
                        if (hasil.data.status !== lacak.dataset.status) {
                            window.location.reload();
                        }
                    });
                });
            </script>
        @endpush
    @endunless
</x-layouts.app>
