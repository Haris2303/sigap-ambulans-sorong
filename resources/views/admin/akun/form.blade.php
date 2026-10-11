<x-layouts.app :judul="($baru ? 'Tambah ' : 'Ubah ').$judulAkun">
    <x-ui.judul-halaman :judul="$baru ? 'Tambah '.$judulAkun : 'Ubah '.$akun->name" />

    <form method="POST" action="{{ $baru ? route($rute.'.store') : route($rute.'.update', $akun) }}">
        @csrf
        @unless ($baru)
            @method('PUT')
        @endunless
        <x-ui.panel class="max-w-xl">
            <div class="space-y-5">
                <x-ui.isian nama="name" label="Nama lengkap" :nilai="$akun->name" :wajib="true" />
                <x-ui.isian nama="email" label="Email" tipe="email" :nilai="$akun->email" :wajib="true" />
                <x-ui.isian nama="no_telepon" label="Nomor telepon" tipe="tel" :nilai="$akun->no_telepon" />
                <x-ui.pilihan nama="faskes_id" label="Faskes" :opsi="$opsiFaskes" :nilai="$akun->faskes_id" kosong="Pilih Faskes" :wajib="true" />
                <x-ui.isian nama="password" label="Kata sandi" tipe="password" :wajib="$baru"
                    :bantuan="$baru ? 'Paling sedikit 8 karakter.' : 'Kosongkan bila tidak diubah.'" autocomplete="new-password" />
                <x-ui.centang nama="aktif" label="Akun aktif" :nilai="$akun->aktif" />
            </div>
            <x-slot:kaki>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
                <x-ui.tombol nada="hantu" :href="route($rute.'.index')">Batal</x-ui.tombol>
            </x-slot:kaki>
        </x-ui.panel>
    </form>
</x-layouts.app>
