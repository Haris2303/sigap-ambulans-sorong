<x-layouts.publik judul="Beranda">
    <div class="max-w-prose">
        <x-ui.judul-halaman judul="Pesan ambulans Dinas Kesehatan Kota Sorong." />
        <p class="text-body-l">SIGAP Ambulans menerima pesanan ambulans dari Masyarakat untuk keadaan darurat, rujukan, dan persalinan. Dinas Kesehatan memverifikasi setiap pesanan, lalu menugaskan ambulans dan sopir dari Fasilitas Kesehatan terdekat.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <x-ui.tombol :href="$aksiUtama['url']">{{ $aksiUtama['label'] }}</x-ui.tombol>
            <x-ui.tombol nada="sekunder" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
        </div>

        <x-ui.bagian judul="Cara kerja">
            <ol class="list-decimal space-y-2 pl-5 text-body-l">
                <li>Masyarakat mengisi pesanan dan lokasi jemput.</li>
                <li>Dinkes memverifikasi pesanan.</li>
                <li>Dinkes menugaskan ambulans dan sopir.</li>
                <li>Masyarakat memantau perjalanan ambulans sampai tiba.</li>
            </ol>
        </x-ui.bagian>
    </div>
</x-layouts.publik>
