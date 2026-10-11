<x-layouts.app :judul="$baru ? 'Tambah Ambulans' : 'Ubah Ambulans'">
    <x-ui.judul-halaman :judul="$baru ? 'Tambah Ambulans' : 'Ubah '.$ambulans->plat_nomor" />

    <form method="POST" action="{{ $baru ? route('admin.ambulans.store') : route('admin.ambulans.update', $ambulans) }}">
        @csrf
        @unless ($baru)
            @method('PUT')
        @endunless
        <x-ui.panel class="max-w-xl">
            <div class="space-y-5">
                <x-ui.isian nama="plat_nomor" label="Plat nomor" :nilai="$ambulans->plat_nomor" :wajib="true" />
                <x-ui.pilihan nama="faskes_id" label="Faskes" :opsi="$opsiFaskes" :nilai="$ambulans->faskes_id" kosong="Pilih Faskes" :wajib="true" />
                <x-ui.pilihan nama="jenis" label="Jenis" :opsi="\App\Enums\JenisAmbulans::opsi()" :nilai="$ambulans->jenis" kosong="Pilih jenis" :wajib="true" />
                <x-ui.isian nama="merek_model" label="Merek dan model" :nilai="$ambulans->merek_model" />
                <x-ui.pilihan nama="sopir_id" label="Sopir tetap" :opsi="$opsiSopir" :nilai="$ambulans->sopir_id" kosong="Tanpa sopir tetap" bantuan="Sopir harus berasal dari Faskes yang sama dengan ambulans." />
                @if ($ambulans->status === \App\Enums\StatusAmbulans::Bertugas)
                    <div>
                        <p class="mb-1 text-label-l">Status</p>
                        <x-ui.lencana :nada="$ambulans->status->nada()">{{ $ambulans->status->label() }}</x-ui.lencana>
                        <p class="mt-1 text-body-s text-on-surface-variant">Status berubah otomatis setelah pesanan selesai atau dibatalkan.</p>
                    </div>
                @else
                    <x-ui.pilihan nama="status" label="Status" :opsi="\App\Enums\StatusAmbulans::opsiManual()" :nilai="$ambulans->status" />
                @endif
            </div>
            <x-slot:kaki>
                <x-ui.tombol tipe="submit">Simpan</x-ui.tombol>
                <x-ui.tombol nada="hantu" :href="route('admin.ambulans.index')">Batal</x-ui.tombol>
            </x-slot:kaki>
        </x-ui.panel>
    </form>
</x-layouts.app>
