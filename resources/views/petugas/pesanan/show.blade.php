{{-- Detail pesanan Faskes arah B (docs/RETEMA_B.md 8.2): sama pola dengan detail Dinkes, tanpa aksi (Faskes hanya melihat). --}}
<x-layouts.app :judul="'Pesanan '.$pesanan->kode" :peta="$pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null">
    @php
        $dibuat = $pesanan->created_at->timezone(config('app.timezone'));
    @endphp
    <x-ui.judul-halaman judul="Pesanan" deskripsi="Detail pesanan dan linimasa status.">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ikon="arrow-left" :href="route('petugas.pesanan.index')">Daftar pesanan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <x-ui.panel>
        <div class="flex flex-wrap items-center gap-2.5">
            <h2 class="text-title-xl tabular-nums">{{ $pesanan->kode }}</h2>
            <x-ui.lencana-status :status="$pesanan->status" />
            @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                <x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana>
            @endif
        </div>
        <p class="mt-1 text-body-s text-on-surface-variant">Dibuat {{ $dibuat->translatedFormat('j M Y') }}, {{ $dibuat->format('H.i') }} WIT</p>

        <div class="mt-6 grid gap-8 md:grid-cols-2">
            <div class="min-w-0">
                <h3 class="mb-3 text-title-m">Data pesanan</h3>
                <x-ui.rincian :bingkai="false" class="-mx-6">
                    <x-ui.rincian-baris label="Pasien">{{ $pesanan->nama_pasien }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Nomor kontak"><span class="tabular-nums">{{ $pesanan->no_telepon_kontak }}</span></x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Layanan">{{ $pesanan->jenis_layanan->label() }}@if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat), {{ \Illuminate\Support\Str::lower($pesanan->tingkat_darurat->label()) }}@endif</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Alamat jemput">{{ $pesanan->lokasi_jemput }}</x-ui.rincian-baris>
                    <x-ui.rincian-baris label="Faskes tujuan">{{ $pesanan->faskesTujuan?->nama ?? 'Belum ditentukan oleh pemohon.' }}</x-ui.rincian-baris>
                    @if ($pesanan->ambulans)
                        <x-ui.rincian-baris label="Ambulans dan sopir">{{ $pesanan->ambulans->plat_nomor }} ({{ $pesanan->ambulans->faskes->nama }}), {{ $pesanan->sopir?->name ?? '-' }}</x-ui.rincian-baris>
                    @endif
                    <x-ui.rincian-baris label="Catatan">{{ $pesanan->catatan ?: '-' }}</x-ui.rincian-baris>
                </x-ui.rincian>

                @if ($pesanan->lat_jemput !== null && $pesanan->lng_jemput !== null)
                    <div class="mt-4">
                        <x-peta.pelacak :lat="$pesanan->lat_jemput" :lng="$pesanan->lng_jemput" />
                    </div>
                @endif
            </div>

            <div class="min-w-0">
                <h3 class="mb-3 text-title-m">Linimasa</h3>
                <x-ui.linimasa :log="$pesanan->log" />
            </div>
        </div>
    </x-ui.panel>
</x-layouts.app>
