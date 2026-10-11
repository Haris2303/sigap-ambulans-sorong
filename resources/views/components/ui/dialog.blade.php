@props([
    'id' => 'dialog-konfirmasi',
    'judul' => 'Konfirmasi',
    'pesan' => '',
    'aksi' => 'Lanjutkan',
    'nada' => 'bahaya',
])
{{-- Dialog arah B (DESIGN.md 6.11): putih, sudut 28px, shadow-overlay, padding 24px, lebar maksimum 28rem, judul
     title-l, isi body-m. Backdrop scrim/45. Satu instance dipakai bersama semua elemen data-konfirmasi (diisi sigap.js). --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" class="m-auto w-full max-w-md rounded-2xl bg-surface-container-lowest p-6 text-on-surface shadow-overlay backdrop:bg-scrim/45">
    <h2 id="{{ $id }}-judul" class="text-title-l" data-dialog-judul>{{ $judul }}</h2>
    <p class="mt-4 text-body-m text-on-surface-variant" data-dialog-pesan>{{ $pesan }}</p>
    <div class="mt-6 flex justify-end gap-2">
        <x-ui.tombol nada="hantu" data-dialog-batal>Batal</x-ui.tombol>
        <x-ui.tombol :nada="$nada === 'bahaya' ? 'bahaya' : 'hantu'" data-dialog-aksi>{{ $aksi }}</x-ui.tombol>
    </div>
</dialog>
