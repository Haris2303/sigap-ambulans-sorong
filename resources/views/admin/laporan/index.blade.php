<x-layouts.app judul="Laporan">
    <x-ui.judul-halaman judul="Laporan" deskripsi="Rekap pesanan berdasarkan tanggal pesanan dibuat.">
        <x-slot:aksi>
            <x-ui.tombol nada="sekunder" ikon="file-text" :href="route('admin.laporan.pdf', ['dari' => $dari, 'sampai' => $sampai])">Unduh PDF</x-ui.tombol>
            <x-ui.tombol nada="sekunder" ikon="download" :href="route('admin.laporan.csv', ['dari' => $dari, 'sampai' => $sampai])">Unduh CSV</x-ui.tombol>
        </x-slot:aksi>
    </x-ui.judul-halaman>

    <form method="GET" action="{{ route('admin.laporan.index') }}" class="mb-6 grid gap-3 sm:grid-cols-3">
        <x-ui.isian nama="dari" label="Dari tanggal" tipe="date" :nilai="$dari" :wajib="true" />
        <x-ui.isian nama="sampai" label="Sampai tanggal" tipe="date" :nilai="$sampai" :wajib="true" />
        <div class="flex items-end">
            <x-ui.tombol nada="sekunder" tipe="submit">Tampilkan</x-ui.tombol>
        </div>
    </form>

    <div class="flex flex-wrap gap-5">
        <x-ui.stat tone="primary" icon="clipboard-list" :value="$data['total']" label="Total pesanan" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat tone="success" icon="circle-check" :value="$data['per_status'][\App\Enums\PesananStatus::Selesai->label()]" label="Selesai" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat tone="warning" icon="clock" :value="\App\Services\LaporanPesanan::formatDurasi($data['respons']['rata'])" label="Rata-rata waktu respons" :hint="$data['respons']['jumlah'].' pesanan ditugaskan'" class="min-w-0 flex-[1_1_14rem]" />
        <x-ui.stat tone="primary" icon="ambulance" :value="\App\Services\LaporanPesanan::formatDurasi($data['durasi']['rata'])" label="Rata-rata durasi layanan" :hint="$data['durasi']['jumlah'].' pesanan selesai'" class="min-w-0 flex-[1_1_14rem]" />
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <x-ui.bagian judul="Pesanan per status">
            <x-ui.panel>
                <x-ui.chart type="doughnut" title="Pesanan per status" unit="pesanan" center-label="pesanan" :segments="$segmenStatus" />
            </x-ui.panel>
        </x-ui.bagian>

        <x-ui.bagian judul="Pesanan per jenis layanan">
            <x-ui.panel>
                <x-ui.chart type="doughnut" title="Pesanan per jenis layanan" unit="pesanan" center-label="pesanan" :segments="$segmenJenis" />
            </x-ui.panel>
        </x-ui.bagian>
    </div>

    <x-ui.bagian judul="Penugasan per Faskes asal">
        @if (empty($data['per_faskes']))
            <x-ui.kosong pesan="Belum ada penugasan pada periode ini." />
        @else
            <x-ui.panel>
                <x-ui.chart type="bar" title="Penugasan per Faskes asal" unit="pesanan"
                    :labels="$grafikFaskes['labels']"
                    :datasets="[['label' => 'Pesanan', 'data' => $grafikFaskes['data'], 'color' => 'primary']]" />
            </x-ui.panel>
        @endif
    </x-ui.bagian>

    <x-ui.bagian judul="Waktu respons dan durasi layanan" deskripsi="Respons dihitung dari pesanan dibuat sampai ditugaskan. Durasi dihitung dari ditugaskan sampai selesai.">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-ui.panel>
                <h3 class="mb-3 text-title-m">Waktu respons</h3>
                <x-ui.chart type="bar" title="Waktu respons" unit="menit"
                    :labels="$grafikRespons['labels']"
                    :datasets="[['label' => 'Respons', 'data' => $grafikRespons['data'], 'valueLabels' => $grafikRespons['valueLabels'], 'color' => 'primary']]" />
            </x-ui.panel>
            <x-ui.panel>
                <h3 class="mb-3 text-title-m">Durasi layanan</h3>
                <x-ui.chart type="bar" title="Durasi layanan" unit="menit"
                    :labels="$grafikDurasi['labels']"
                    :datasets="[['label' => 'Durasi', 'data' => $grafikDurasi['data'], 'valueLabels' => $grafikDurasi['valueLabels'], 'color' => 'tertiary']]" />
            </x-ui.panel>
        </div>
    </x-ui.bagian>
</x-layouts.app>
