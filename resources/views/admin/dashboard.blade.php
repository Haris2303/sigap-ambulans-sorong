{{-- Dasbor Dinkes (docs/DESAIN_DASBOR_MATERIAL.md bagian 6). Data dari App\Services\RingkasanDasbor. --}}
<x-layouts.app judul="Dasbor">
    <x-ui.judul-halaman judul="Dasbor" deskripsi="Ringkasan pesanan dan ketersediaan Faskes. Angka diperbarui otomatis.">
        <x-slot:aksi>
            <x-ui.tombol nada="tonal" :href="route('admin.ketersediaan.index')">Kelola ketersediaan</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    {{-- Baris statistik: kartu utama primary dan tiga kartu putih. --}}
    <div class="flex flex-wrap gap-5">
        <x-ui.stat :featured="true" data-ringkasan="pesanan_menunggu" icon="clock" :value="$statistik['pesanan_menunggu']"
            :label="\App\Enums\PesananStatus::Menunggu->label()" hint="pesanan baru" class="min-w-0 flex-[1.5_1_18rem]">
            {{-- Pesanan Gawat yang menunggu: tampil bila ada, ikut polling (data-angka dan data-sembunyi-nol). Daftar "Perlu tindakan" sudah menaruh Gawat paling atas. --}}
            <x-slot:penanda>
                <x-ui.lencana nada="bahaya" data-sembunyi-nol="pesanan_menunggu_gawat" :hidden="$statistik['pesanan_menunggu_gawat'] < 1"><span data-angka="pesanan_menunggu_gawat" class="tabular-nums">{{ $statistik['pesanan_menunggu_gawat'] }}</span> {{ \App\Enums\TingkatDarurat::Gawat->label() }}</x-ui.lencana>
            </x-slot:penanda>
            <x-slot:aksi>
                <x-ui.tombol nada="on-primary" :href="route('admin.pesanan.index', ['tab' => 'perlu-tindakan'])">Verifikasi pesanan</x-ui.tombol>
            </x-slot:aksi>
        </x-ui.stat>
        <x-ui.stat data-ringkasan="pesanan_terverifikasi" tone="primary" icon="user-check" :value="$statistik['pesanan_terverifikasi']"
            :label="\App\Enums\PesananStatus::Terverifikasi->label()" hint="menunggu penugasan" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat data-ringkasan="pesanan_berjalan" tone="warning" icon="ambulance" :value="$statistik['pesanan_berjalan']"
            label="Sedang berjalan" hint="ditugaskan sampai di jalan" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat data-ringkasan="pesanan_selesai_hari_ini" tone="success" icon="circle-check" :value="$statistik['pesanan_selesai_hari_ini']"
            label="Selesai hari ini" hint="pesanan tuntas" class="min-w-0 flex-[1_1_14rem]" />
    </div>

    {{-- Baris grafik: batang 7 hari (2 bagian) dan donat status (1 bagian). --}}
    <div class="mt-5 flex flex-wrap gap-5">
        <x-ui.panel class="min-w-0 flex-[2_1_24rem]">
            <h2 class="text-card-title">Pesanan 7 hari terakhir</h2>
            <p class="text-body-m text-on-surface-variant">{{ $batang['rentang'] }}</p>
            <x-ui.chart.bar class="mt-2" :labels="$batang['labels']" :values="$batang['values']" :highlight="$batang['highlight']"
                title="Grafik batang pesanan per hari" unit="pesanan" />
        </x-ui.panel>
        <x-ui.panel class="min-w-0 flex-[1_1_20rem]">
            <h2 class="text-card-title">Status pesanan</h2>
            <p class="text-body-m text-on-surface-variant">{{ $donat['total'] }} pesanan terbaru</p>
            <x-ui.chart.donut class="mt-2" :segments="$donat['segments']" center-label="pesanan"
                :title="'Diagram cincin status '.$donat['total'].' pesanan terbaru'" />
        </x-ui.panel>
    </div>

    {{-- Baris bawah: ketersediaan (petak 2x2) dan pesanan terbaru (tab dan tabel). --}}
    <div class="mt-5 flex flex-wrap gap-5">
        <x-ui.panel class="min-w-0 flex-[1_1_21rem]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-card-title">Ketersediaan</h2>
                    <p class="text-body-m text-on-surface-variant">Semua Faskes, diperbarui otomatis</p>
                </div>
                <a href="{{ route('admin.ketersediaan.index') }}" class="shrink-0 text-label-l text-primary underline-offset-2 hover:underline">Kelola</a>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.tile data-ringkasan="ambulans_tersedia" tone="primary" icon="ambulance" :value="$ketersediaan['ambulans_tersedia']" label="Ambulans tersedia" />
                <x-ui.tile data-ringkasan="bed_igd_tersedia" tone="error" icon="bed-double" :value="$ketersediaan['bed_igd_tersedia']" label="Bed IGD tersedia" />
                <x-ui.tile data-ringkasan="bed_rawat_inap_tersedia" tone="warning" icon="bed-double" :value="$ketersediaan['bed_rawat_inap_tersedia']" label="Bed rawat inap tersedia" />
                <x-ui.tile data-ringkasan="dokter_tersedia" tone="success" icon="stethoscope" :value="$ketersediaan['dokter_tersedia']" label="Dokter tersedia" />
            </div>
        </x-ui.panel>

        <x-ui.panel :flush="true" id="pesanan-terbaru" class="min-w-0 flex-[2_1_28rem] scroll-mt-28">
            <div class="flex items-start justify-between gap-3 px-6 pt-6">
                <div class="min-w-0">
                    <h2 class="text-card-title">Pesanan terbaru</h2>
                    <p class="text-body-m text-on-surface-variant">Menampilkan {{ $terbaru['jumlah_tampil'] }} dari {{ $terbaru['jumlah_jendela'] }} pesanan terbaru</p>
                </div>
                <a href="{{ route('admin.pesanan.index') }}" class="shrink-0 text-label-l text-primary underline-offset-2 hover:underline">Semua pesanan</a>
            </div>

            <x-ui.tabs label="Filter pesanan terbaru" class="mt-4 px-6">
                @foreach (\App\Enums\TabPesanan::cases() as $tab)
                    <x-ui.tab :href="route('admin.dashboard', ['tab' => $tab->value]).'#pesanan-terbaru'" :aktif="$terbaru['tab'] === $tab">{{ $tab->label() }}</x-ui.tab>
                @endforeach
            </x-ui.tabs>

            @if ($terbaru['baris']->isEmpty())
                <p class="px-6 py-8 text-body-l text-on-surface-variant">Belum ada pesanan di tab ini.</p>
            @else
                <x-ui.tabel :bingkai="false" lebar-min="min-w-136">
                    <x-slot:kepala>
                        <th>Kode</th>
                        <th>Pasien</th>
                        <th>Layanan</th>
                        <th>Status</th>
                    </x-slot:kepala>
                    @foreach ($terbaru['baris'] as $pesanan)
                        <tr>
                            <td>
                                <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="tabular-nums text-primary underline underline-offset-2 hover:text-on-primary-container">{{ $pesanan->kode }}</a>
                                <x-ui.waktu :nilai="$pesanan->created_at" format="d M Y, H:i" class="block text-body-s text-on-surface-variant" />
                                @if ($pesanan->status->menungguPenugasan())
                                    <div class="mt-1"><x-ui.waktu-tunggu :pesanan="$pesanan" /></div>
                                @endif
                            </td>
                            <td>{{ $pesanan->nama_pasien }}</td>
                            <td><span class="inline-flex items-center gap-2">{{ $pesanan->jenis_layanan->label() }}@if ($pesanan->tingkat_darurat === \App\Enums\TingkatDarurat::Gawat) <x-ui.lencana nada="bahaya">{{ $pesanan->tingkat_darurat->label() }}</x-ui.lencana>@endif</span></td>
                            <td><x-ui.lencana-status :status="$pesanan->status" /></td>
                        </tr>
                    @endforeach
                </x-ui.tabel>
            @endif
        </x-ui.panel>
    </div>
</x-layouts.app>
