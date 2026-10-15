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

    <x-ui.bagian judul="Pesanan per status">
        <x-ui.panel :flush="true">
            <x-ui.tabel :bingkai="false" class="px-5 pb-5 pt-2 lg:px-6 lg:pb-6">
                <x-slot:kepala><th>Status</th><th class="text-right">Jumlah</th></x-slot:kepala>
                @foreach ($data['per_status'] as $label => $jumlah)
                    <tr><td>{{ $label }}</td><td class="text-right tabular-nums">{{ $jumlah }}</td></tr>
                @endforeach
            </x-ui.tabel>
        </x-ui.panel>
    </x-ui.bagian>

    <x-ui.bagian judul="Pesanan per jenis layanan">
        <x-ui.panel :flush="true">
            <x-ui.tabel :bingkai="false" class="px-5 pb-5 pt-2 lg:px-6 lg:pb-6">
                <x-slot:kepala><th>Jenis layanan</th><th class="text-right">Jumlah</th></x-slot:kepala>
                @foreach ($data['per_jenis'] as $label => $jumlah)
                    <tr><td>{{ $label }}</td><td class="text-right tabular-nums">{{ $jumlah }}</td></tr>
                @endforeach
            </x-ui.tabel>
        </x-ui.panel>
    </x-ui.bagian>

    <x-ui.bagian judul="Penugasan per Faskes asal">
        @if (empty($data['per_faskes']))
            <x-ui.kosong pesan="Belum ada penugasan pada periode ini." />
        @else
            <x-ui.panel :flush="true">
                <x-ui.tabel :bingkai="false" class="px-5 pb-5 pt-2 lg:px-6 lg:pb-6">
                    <x-slot:kepala><th>Faskes</th><th class="text-right">Jumlah</th></x-slot:kepala>
                    @foreach ($data['per_faskes'] as $nama => $jumlah)
                        <tr><td>{{ $nama }}</td><td class="text-right tabular-nums">{{ $jumlah }}</td></tr>
                    @endforeach
                </x-ui.tabel>
            </x-ui.panel>
        @endif
    </x-ui.bagian>

    <x-ui.bagian judul="Waktu respons dan durasi layanan" deskripsi="Respons dihitung dari pesanan dibuat sampai ditugaskan. Durasi dihitung dari ditugaskan sampai selesai.">
        <x-ui.panel :flush="true">
            <x-ui.tabel :bingkai="false" lebar-min="min-w-100" class="px-5 pb-5 pt-2 lg:px-6 lg:pb-6">
                <x-slot:kepala><th>Ukuran</th><th>Respons</th><th>Durasi layanan</th></x-slot:kepala>
                @foreach (['rata' => 'Rata-rata', 'tercepat' => 'Tercepat', 'terlama' => 'Terlama'] as $kunci => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="tabular-nums">{{ \App\Services\LaporanPesanan::formatDurasi($data['respons'][$kunci]) }}</td>
                        <td class="tabular-nums">{{ \App\Services\LaporanPesanan::formatDurasi($data['durasi'][$kunci]) }}</td>
                    </tr>
                @endforeach
            </x-ui.tabel>
        </x-ui.panel>
    </x-ui.bagian>
</x-layouts.app>
