{{-- Detail pesanan Masyarakat arah B (docs/RETEMA_B.md 8.2): sama pola dengan detail Dinkes/Faskes, aksi hanya Batalkan. --}}
<x-layouts.app :judul="'Pesanan '.$pesanan->kode" :peta="$tampilkanPeta">
    @php
        $dibuat = $pesanan->created_at->timezone(config('app.timezone'));
    @endphp
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Detail pesanan dan linimasa status.">
        <x-slot:aksi>
            @unless ($pesanan->status->final())
                <x-ui.kontak-darurat />
            @endunless
            <x-ui.tombol nada="sekunder" ikon="arrow-left" :href="route('warga.pesanan.index')">Riwayat pesanan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.panel data-lacak data-status="{{ $pesanan->status->value }}"
        data-url="{{ route('warga.pesanan.status', $pesanan) }}" data-detik="{{ config('sigap.polling.warga_detik') }}">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-title-xl tabular-nums">{{ $pesanan->kode }}</h2>
                    <x-ui.lencana-status :status="$pesanan->status" />
                    @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                        <x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana>
                    @endif
                </div>
                <p class="mt-1 text-body-s text-on-surface-variant">Dibuat {{ $dibuat->translatedFormat('j M Y') }}, {{ $dibuat->format('H.i') }} WIT</p>
            </div>

            @if ($bolehBatal)
                <x-ui.tombol nada="bahaya" data-dialog-buka="dialog-batalkan">Batalkan</x-ui.tombol>
            @endif
        </div>

        <div class="mt-6 grid gap-8 md:grid-cols-2">
            <div class="min-w-0">
                <h3 class="mb-3 text-title-m">Data pesanan</h3>
                <x-ui.rincian :bingkai="false" class="-mx-6">
                    <x-ui.rincian-baris label="Pasien">{{ $pesanan->nama_pasien }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Nomor kontak"><span class="tabular-nums">{{ $pesanan->no_telepon_kontak }}</span></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Layanan">{{ $pesanan->jenis_layanan->label() }}@if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat), {{ \Illuminate\Support\Str::lower($pesanan->tingkat_darurat->label()) }}@endif</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Alamat jemput">{{ $pesanan->lokasi_jemput }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes tujuan">{{ $pesanan->faskesTujuan?->nama ?? 'Belum ditentukan oleh pemohon.' }}</x-ui.rincian-baris>
                    @if ($tampilkanSopir)
                        <x-ui.rincian-baris label="Ambulans dan sopir">{{ $pesanan->ambulans->plat_nomor }} ({{ $pesanan->ambulans->faskes->nama }}), {{ $pesanan->sopir->name }}@if ($pesanan->sopir->no_telepon) (<a href="tel:{{ $pesanan->sopir->no_telepon }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->sopir->no_telepon }}</a>)@endif</x-ui.rincian-baris>
                    @endif
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

                @if ($tampilkanPeta)
                    <div class="mt-4">
                        <h3 class="mb-2 text-title-m">Posisi ambulans</h3>
                        <x-peta.pelacak :lat="$pesanan->lat_jemput" :lng="$pesanan->lng_jemput" :url-posisi="route('warga.pesanan.status', $pesanan)" />
                    </div>
                @endif
            </div>

            <div class="min-w-0">
                <h3 class="mb-3 text-title-m">Linimasa</h3>
                <x-ui.linimasa :log="$pesanan->log" />
            </div>
        </div>
    </x-ui.panel>

    @if ($bolehBatal)
        <x-ui.dialog id="dialog-batalkan" :judul="'Batalkan pesanan '.$pesanan->kode.'?'">
            <p class="mt-2 text-body-m text-on-surface-variant">Pesanan ini akan dibatalkan dan tidak dapat dipulihkan. Pesanan hanya dapat dibatalkan sebelum ambulans ditugaskan.</p>
            <form method="POST" action="{{ route('warga.pesanan.batalkan', $pesanan) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <x-ui.area-teks nama="alasan" label="Alasan (opsional)" />
                <div class="flex justify-end gap-2">
                    <x-ui.tombol nada="hantu" tipe="button" data-dialog-tutup>Batal</x-ui.tombol>
                    <x-ui.tombol nada="bahaya" tipe="submit" memuat="Membatalkan...">Batalkan pesanan</x-ui.tombol>
                </div>
            </form>
        </x-ui.dialog>
    @endif

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
