<x-layouts.publik judul="Beranda">
    <div class="max-w-prose">
        <div class="rounded-2xl bg-secondary p-6 text-on-secondary shadow-card lg:p-8">
            <h1 class="text-page-title max-lg:text-page-title-s">Pesan ambulans Dinas Kesehatan Kota Sorong.</h1>
            <p class="mt-3 text-body-l text-primary-container">SIGAP Ambulans menerima pesanan ambulans dari Masyarakat untuk keadaan darurat, rujukan, dan persalinan. Dinas Kesehatan memverifikasi setiap pesanan, lalu menugaskan ambulans dan sopir dari Fasilitas Kesehatan terdekat.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <x-ui.tombol nada="on-secondary" :href="$aksiUtama['url']">{{ $aksiUtama['label'] }}</x-ui.tombol>
                <x-ui.tombol nada="on-secondary" :href="route('faskes.index')">Info Faskes</x-ui.tombol>
            </div>
        </div>
        <x-ui.kontak-darurat class="mt-6">Keadaan darurat dan aplikasi ini tidak bisa dipakai?</x-ui.kontak-darurat>

        <x-ui.bagian judul="Cara kerja">
            <x-ui.panel>
                <ol class="list-decimal space-y-2 pl-5 text-body-l">
                    <li>Masyarakat mengisi pesanan dan lokasi jemput.</li>
                    <li>Dinkes memverifikasi pesanan.</li>
                    <li>Dinkes menugaskan ambulans dan sopir.</li>
                    <li>Masyarakat memantau perjalanan ambulans sampai tiba.</li>
                </ol>
            </x-ui.panel>
        </x-ui.bagian>
    </div>
</x-layouts.publik>
