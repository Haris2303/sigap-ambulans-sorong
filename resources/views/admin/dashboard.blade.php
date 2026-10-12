{{-- Dasbor Dinkes arah B (docs/RETEMA_B.md bagian 8.1). Data dari App\Services\RingkasanDasbor. --}}
<x-layouts.app judul="Dasbor">
    {{-- Kepala halaman dan baris statistik dalam satu penanda data-shot="stat" (potongan rujukan B1 mencakup keduanya). --}}
    <div data-shot="stat">
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
    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
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

    {{-- Baris bawah (docs/RETEMA_B.md 8.1 butir 3): Pesanan terbaru (8 kolom, tabel 6.9) dan Ketersediaan (4 kolom, ring-tile 6.10). --}}
    <div data-shot="bawah" class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-12">
        <x-ui.panel :flush="true" id="pesanan-terbaru" class="xl:col-span-8 scroll-mt-28">
            <div class="flex flex-wrap items-start justify-between gap-3 px-5 pt-5 lg:px-6 lg:pt-6">
                <div>
                    <h2 class="text-title-l">Pesanan terbaru</h2>
                    <p class="text-body-s text-on-surface-variant">{{ $terbaru['jumlah_hari_ini'] }} pesanan terakhir hari ini</p>
                </div>
                <x-ui.tabs label="Filter pesanan terbaru">
                    @foreach (\App\Enums\TabPesanan::cases() as $t)
                        <x-ui.tab :href="route('admin.dashboard', array_merge(request()->only('periode'), ['tab' => $t->value])).'#pesanan-terbaru'" :aktif="$terbaru['tab'] === $t">{{ $t->label() }}</x-ui.tab>
                    @endforeach
                </x-ui.tabs>
            </div>

            <div class="px-5 pb-5 pt-4 lg:px-6 lg:pb-6">
                @if ($terbaru['baris']->isEmpty())
                    <x-ui.kosong pesan="Belum ada pesanan di tab ini." />
                @else
                    <x-ui.tabel :bingkai="false" class="hidden md:block">
                        <x-slot:kepala>
                            <th>Pasien</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Jam</th>
                            <th><span class="sr-only">Buka</span></th>
                        </x-slot:kepala>
                        @foreach ($terbaru['baris'] as $pesanan)
                            <tr class="relative">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :nama="$pesanan->nama_pasien" />
                                        <div class="min-w-0">
                                            <p class="truncate text-label-l"><a href="{{ route('admin.pesanan.show', $pesanan) }}" class="after:absolute after:inset-0">{{ $pesanan->nama_pasien }}</a></p>
                                            <p class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->kode }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        {{ $pesanan->jenis_layanan->label() }}
                                        @if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat)
                                            <x-ui.lencana nada="bahaya" size="sm">{{ \App\Enums\TingkatDarurat::Gawat->label() }}</x-ui.lencana>
                                        @endif
                                    </span>
                                </td>
                                <td><x-ui.lencana-status :status="$pesanan->status" /></td>
                                <td class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->created_at->format('H.i') }}</td>
                                <td aria-hidden="true"><x-ui.ikon nama="chevron-right" :ukuran="18" class="text-on-surface-variant" /></td>
                            </tr>
                        @endforeach
                    </x-ui.tabel>

                    {{-- Daftar baris di bawah md (docs/RETEMA_B.md 6.9), markup tersendiri untuk kartu ini (bukan x-ui.table generik, lihat RETEMA_B.md "DB"). --}}
                    <ul role="list" class="md:hidden">
                        @foreach ($terbaru['baris'] as $pesanan)
                            <li class="relative grid grid-cols-[40px_1fr_auto] items-center gap-3 border-t border-surface-container-highest py-3 first:border-t-0">
                                <x-ui.avatar :nama="$pesanan->nama_pasien" />
                                <div class="min-w-0">
                                    <p class="truncate text-label-l"><a href="{{ route('admin.pesanan.show', $pesanan) }}" class="after:absolute after:inset-0">{{ $pesanan->nama_pasien }}</a></p>
                                    <p class="truncate text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->kode }}, {{ $pesanan->jenis_layanan->label() }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <x-ui.lencana-status :status="$pesanan->status" size="sm" />
                                    <span class="text-body-s text-on-surface-variant tabular-nums">{{ $pesanan->created_at->format('H.i') }}@if ($pesanan->status->menungguPenugasan()), menunggu {{ \App\Support\WaktuTunggu::menit($pesanan->created_at) }} mnt @endif</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </x-ui.panel>

        <x-ui.panel class="xl:col-span-4">
            <h2 class="text-title-l">Ketersediaan</h2>
            <p class="mb-4 text-body-s text-on-surface-variant">Tersedia dari total, semua Faskes</p>
            <div class="grid grid-cols-2 gap-3">
                <x-ui.ring-tile :tersedia="$ketersediaan['ambulans']['tersedia']" :total="$ketersediaan['ambulans']['total']" label="Ambulans" />
                <x-ui.ring-tile :tersedia="$ketersediaan['bed_igd']['tersedia']" :total="$ketersediaan['bed_igd']['total']" label="Bed IGD" />
                <x-ui.ring-tile :tersedia="$ketersediaan['bed_rawat_inap']['tersedia']" :total="$ketersediaan['bed_rawat_inap']['total']" label="Bed rawat inap" />
                <x-ui.ring-tile :tersedia="$ketersediaan['dokter']['tersedia']" :total="$ketersediaan['dokter']['total']" label="Dokter" />
            </div>
        </x-ui.panel>
    </div>
</x-layouts.app>
