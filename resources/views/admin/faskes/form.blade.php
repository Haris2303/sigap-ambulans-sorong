<x-layouts.app :judul="$baru ? 'Tambah Faskes' : 'Ubah Faskes'" :peta="true">
    <x-ui.judul-halaman :judul="$baru ? 'Tambah Faskes' : 'Ubah '.$faskes->nama" deskripsi="Titik koordinat dipakai untuk peta pada Info Faskes." />

    <form method="POST" action="{{ $baru ? route('admin.faskes.store') : route('admin.faskes.update', $faskes) }}">
        @csrf
        @unless ($baru)
            @method('PUT')
        @endunless
        <x-ui.panel class="max-w-xl">
            <div class="space-y-5">
                <x-ui.isian nama="nama" label="Nama Faskes" :nilai="$faskes->nama" :wajib="true" />
                <x-ui.pilihan nama="jenis" label="Jenis" :opsi="\App\Enums\JenisFaskes::opsi()" :nilai="$faskes->jenis" kosong="Pilih jenis" :wajib="true" />
                <x-ui.pilihan nama="kepemilikan" label="Kepemilikan" :opsi="\App\Enums\Kepemilikan::opsi()" :nilai="$faskes->kepemilikan" kosong="Belum diketahui" />
                <x-ui.isian nama="telepon" label="Telepon" tipe="tel" :nilai="$faskes->telepon" />
                <x-ui.isian nama="alamat" label="Alamat" :nilai="$faskes->alamat" bantuan="Terisi otomatis bila Anda memilih titik di peta." />
                <div>
                    <p class="mb-1 text-label">Titik koordinat</p>
                    <x-peta.pemilih lat="lat" lng="lng" alamat="alamat" :nilai-lat="$faskes->lat" :nilai-lng="$faskes->lng" />
                </div>
                <x-ui.centang nama="aktif" label="Faskes aktif" :nilai="$faskes->aktif" />
            </div>
            <x-slot:kaki>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
                <x-ui.tombol nada="hantu" :href="route('admin.faskes.index')">Batal</x-ui.tombol>
            </x-slot:kaki>
        </x-ui.panel>
    </form>
</x-layouts.app>
