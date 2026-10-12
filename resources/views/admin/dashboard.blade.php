{{-- Dasbor Dinkes arah B (docs/RETEMA_B.md bagian 8.1). Data dari App\Services\RingkasanDasbor. --}}
<x-layouts.app judul="Dasbor">
    <x-ui.judul-halaman :judul="$judulDasbor" :deskripsi="$subjudulDasbor">
        <x-slot:aksi>
            <x-ui.tabs variant="floating" label="Filter periode">
                @foreach (\App\Enums\PeriodeDasbor::cases() as $p)
                    <x-ui.tab variant="floating" :href="route('admin.dashboard', array_merge(request()->only('tab'), ['periode' => $p->value]))" :aktif="$periode === $p">{{ $p->label() }}</x-ui.tab>
                @endforeach
            </x-ui.tabs>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    {{-- Baris statistik (docs/RETEMA_B.md 8.1 butir 1): kartu navy dan tiga kartu putih dengan sparkline. --}}
    <div data-shot="stat" class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
        <x-ui.stat :featured="true" data-ringkasan="pesanan_menunggu" icon="clock" :value="$statistik['pesanan_menunggu']"
            label="Menunggu verifikasi"
            :hint="$statistik['pesanan_menunggu'] > 0 ? 'pesanan, terlama '.$statistik['pesanan_menunggu_tertua_menit'].' menit' : 'tidak ada pesanan menunggu'"
            class="col-span-2 xl:col-span-1">
            <x-slot:penanda>
                <x-ui.lencana nada="bahaya" data-sembunyi-nol="pesanan_menunggu_gawat" :hidden="$statistik['pesanan_menunggu_gawat'] < 1"><span data-angka="pesanan_menunggu_gawat" class="tabular-nums">{{ $statistik['pesanan_menunggu_gawat'] }}</span> {{ \App\Enums\TingkatDarurat::Gawat->label() }}</x-ui.lencana>
            </x-slot:penanda>
            <x-slot:aksi>
                <x-ui.tombol nada="on-secondary" :href="route('admin.pesanan.index', ['tab' => 'perlu-tindakan'])">Verifikasi sekarang</x-ui.tombol>
            </x-slot:aksi>
        </x-ui.stat>
        <x-ui.stat data-ringkasan="pesanan_terverifikasi" tone="primary" icon="user-check" :value="$statistik['pesanan_terverifikasi']"
            label="Terverifikasi" hint="menunggu penugasan" hint-tone="primary" :series="$sparkline['pesanan_terverifikasi']" />
        <x-ui.stat data-ringkasan="pesanan_berjalan" tone="warning" icon="ambulance" :value="$statistik['pesanan_berjalan']"
            label="Sedang berjalan" hint="ditugaskan sampai di jalan" hint-tone="neutral" :series="$sparkline['pesanan_berjalan']" sparkline-tone="chart-amber" />
        <x-ui.stat data-ringkasan="{{ $periode === \App\Enums\PeriodeDasbor::Hari ? 'pesanan_selesai_hari_ini' : '' }}"
            tone="success" icon="circle-check" :value="$statistik['pesanan_selesai']" :label="'Selesai, '.$periode->labelKecil()"
            :hint="($statistik['selesai_delta'] >= 0 ? '+' : '').$statistik['selesai_delta'].' dari '.$periode->labelSebelumnya()"
            :hint-tone="$statistik['selesai_delta'] > 0 ? 'success' : ($statistik['selesai_delta'] < 0 ? 'error' : 'neutral')"
            :series="$sparkline['pesanan_selesai']" sparkline-tone="chart-mint" class="col-span-2 xl:col-span-1" />
    </div>

    {{-- Baris grafik (docs/RETEMA_B.md 8.1 butir 2 dan bagian 7.1): garis Chart.js (8 kolom) dan donat (4 kolom). --}}
    @php
        $rentangGrafik = $periode->perJam() ? 'per jam hari ini' : 'per hari, '.$grafik['tooltip_titles'][0].' sampai '.$grafik['tooltip_titles'][count($grafik['tooltip_titles']) - 1];
    @endphp
    <div data-shot="grafik" class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12">
        <x-ui.panel class="xl:col-span-8">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-title-l">Pesanan {{ $periode->labelKecil() }}</h2>
                    <p class="text-body-s text-on-surface-variant">Masuk dan selesai per hari</p>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex h-8 items-center gap-2 rounded-full bg-surface px-3 text-label-m"><span class="size-2.5 rounded-full bg-primary" aria-hidden="true"></span>Masuk</span>
                    <span class="inline-flex h-8 items-center gap-2 rounded-full bg-surface px-3 text-label-m"><span class="size-2.5 rounded-full bg-chart-mint" aria-hidden="true"></span>Selesai</span>
                </div>
            </div>
            <x-ui.chart type="line" title="Grafik pesanan masuk dan selesai {{ $rentangGrafik }}" unit="pesanan"
                :labels="$grafik['labels']" :tick-step="$grafik['tick_step']" :tooltip-titles="$grafik['tooltip_titles']"
                :datasets="[
                    ['label' => 'Masuk', 'data' => $grafik['masuk'], 'color' => 'primary', 'width' => 3.5, 'fill' => true],
                    ['label' => 'Selesai', 'data' => $grafik['pesanan_selesai'], 'color' => 'chart-mint', 'width' => 3, 'fill' => false],
                ]" />
        </x-ui.panel>
        <x-ui.panel class="xl:col-span-4">
            <h2 class="text-title-l">Status pesanan</h2>
            <p class="mb-2 text-body-s text-on-surface-variant">{{ $periode->labelKecil() }}</p>
            <x-ui.chart type="doughnut" title="Diagram cincin status pesanan {{ $periode->labelKecil() }}" unit="pesanan"
                center-label="pesanan" :segments="$donat['segments']" />
        </x-ui.panel>
    </div>

    {{-- Baris bawah (docs/RETEMA_B.md 8.1 butir 3): akan diganti tabel dan ring-tile pada langkah berikutnya. --}}
    <div data-shot="bawah" class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12">
        <x-ui.panel class="xl:col-span-8">
            <h2 class="text-title-l">Pesanan terbaru</h2>
            <p class="text-body-s text-on-surface-variant">{{ $terbaru['jumlah_hari_ini'] }} pesanan terakhir hari ini</p>
            <ul class="mt-4 space-y-1 text-body-m">
                @foreach ($terbaru['baris'] as $p)
                    <li>{{ $p->nama_pasien }} ({{ $p->kode }}) - {{ $p->status->label() }}</li>
                @endforeach
            </ul>
        </x-ui.panel>
        <x-ui.panel class="xl:col-span-4">
            <h2 class="text-title-l">Ketersediaan</h2>
            <p class="text-body-s text-on-surface-variant">Tersedia dari total, semua Faskes</p>
            <ul class="mt-4 space-y-1 text-body-m">
                @foreach (['ambulans' => 'Ambulans', 'bed_igd' => 'Bed IGD', 'bed_rawat_inap' => 'Bed rawat inap', 'dokter' => 'Dokter'] as $kunci => $label)
                    <li>{{ $label }}: {{ $ketersediaan[$kunci]['tersedia'] }} dari {{ $ketersediaan[$kunci]['total'] }}</li>
                @endforeach
            </ul>
        </x-ui.panel>
    </div>
</x-layouts.app>
