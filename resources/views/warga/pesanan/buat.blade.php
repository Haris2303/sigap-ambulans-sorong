<x-layouts.app judul="Pesan ambulans" :peta="true">
    <x-ui.judul-halaman judul="Pesan ambulans" deskripsi="Isi data pesanan. Anda dapat memeriksanya kembali sebelum dikirim." />

    <form method="POST" action="{{ route('warga.pesanan.tinjau') }}" class="max-w-xl">
        @csrf
        <div class="space-y-8">
            <fieldset>
                <legend class="mb-2 text-h2">Jenis layanan</legend>
                <div class="space-y-2">
                    @foreach (\App\Enums\JenisLayanan::cases() as $layanan)
                        <x-ui.opsi-baris nama="jenis_layanan" :nilai="$layanan->value" :judul="$layanan->label()" :deskripsi="$layanan->deskripsi()"
                            :terpilih="($draf['jenis_layanan'] ?? \App\Enums\JenisLayanan::Darurat->value) === $layanan->value" />
                    @endforeach
                </div>
                @error('jenis_layanan')<p class="mt-1 text-body-sm text-danger">{{ $message }}</p>@enderror
            </fieldset>

            <fieldset>
                <legend class="mb-2 text-h2">Tingkat darurat</legend>
                <div class="space-y-2">
                    @foreach (\App\Enums\TingkatDarurat::cases() as $tingkat)
                        <x-ui.opsi-baris nama="tingkat_darurat" :nilai="$tingkat->value" :judul="$tingkat->label()" :deskripsi="$tingkat->deskripsi()"
                            :terpilih="($draf['tingkat_darurat'] ?? \App\Enums\TingkatDarurat::Biasa->value) === $tingkat->value" />
                    @endforeach
                </div>
                @error('tingkat_darurat')<p class="mt-1 text-body-sm text-danger">{{ $message }}</p>@enderror
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="mb-2 text-h2">Lokasi jemput</legend>
                <x-peta.pemilih :nilai-lat="$draf['lat_jemput'] ?? null" :nilai-lng="$draf['lng_jemput'] ?? null" />
                <x-ui.isian nama="lokasi_jemput" label="Alamat lokasi jemput" :nilai="$draf['lokasi_jemput'] ?? null" bantuan="Tulis nama jalan, patokan, atau kelurahan. Terisi otomatis bila Anda memilih titik di peta." :wajib="true" />
            </fieldset>

            <fieldset>
                <legend class="mb-2 text-h2">Faskes tujuan</legend>
                <x-ui.pilihan nama="faskes_tujuan_id" label="Faskes tujuan"
                    :opsi="$faskes->mapWithKeys(fn ($f) => [$f->id => $f->nama.' ('.$f->jenis->label().')'])->all()"
                    :nilai="$draf['faskes_tujuan_id'] ?? null" kosong="Belum ditentukan"
                    bantuan="Wajib untuk rujukan. Untuk layanan lain boleh dikosongkan." />
                <div class="mt-3">
                    @foreach ($faskes as $f)
                        <div hidden data-faskes="{{ $f->id }}" class="rounded-md border border-line bg-surface p-3">
                            <x-ui.ketersediaan :faskes="$f" />
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="space-y-5">
                <legend class="mb-2 text-h2">Data pasien</legend>
                <x-ui.isian nama="nama_pasien" label="Nama pasien" :nilai="$draf['nama_pasien'] ?? null" :wajib="true" autocomplete="off" />
                <x-ui.isian nama="no_telepon_kontak" label="Nomor kontak yang bisa dihubungi" tipe="tel" :nilai="$noTeleponAwal" :wajib="true" inputmode="tel" />
                <x-ui.area-teks nama="catatan" label="Catatan untuk petugas" :nilai="$draf['catatan'] ?? null" bantuan="Contoh: kondisi pasien, patokan rumah, atau pintu masuk." />
            </fieldset>
        </div>

        <div class="sticky bottom-0 -mx-4 mt-8 border-t border-line bg-surface p-4 md:static md:mx-0 md:border-0 md:bg-transparent md:p-0">
            <x-ui.tombol tipe="submit" ukuran="lg" :blok="true">Tinjau pesanan</x-ui.tombol>
        </div>
    </form>

    @push('skrip')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const pilih = document.getElementById('f-faskes_tujuan_id');
                const tampil = () => document.querySelectorAll('[data-faskes]').forEach((el) => {
                    el.hidden = el.dataset.faskes !== pilih.value;
                });
                pilih.addEventListener('change', tampil);
                tampil();
            });
        </script>
    @endpush
</x-layouts.app>
