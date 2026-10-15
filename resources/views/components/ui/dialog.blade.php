@props([
    'id' => 'dialog-konfirmasi',
    'judul' => 'Konfirmasi',
    'pesan' => '',
    'aksi' => 'Lanjutkan',
    'nada' => 'bahaya',
])
{{-- Dialog arah B (DESIGN.md 6.11): putih, sudut 28px, shadow-overlay, padding 24px, lebar maksimum 28rem, judul
     title-l, isi body-m. Backdrop scrim/45. Tanpa isi ($slot kosong): satu instance dipakai bersama semua elemen
     data-konfirmasi (diisi sigap.js), seperti semula. Dengan isi ($slot terisi, docs/RETEMA_B.md 6.11 dan 8.2): dialog
     aksi pesanan tersendiri (Tolak/Batalkan/Lepas) berisi form sendiri (mis. alasan); dibuka lewat
     data-dialog-buka="{{ $id }}" pada tombol pemicu, ditutup lewat data-dialog-tutup di dalamnya (resources/js/sigap.js). --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" class="m-auto w-full max-w-md rounded-2xl bg-surface-container-lowest p-6 text-on-surface shadow-overlay backdrop:bg-scrim/45">
    <h2 id="{{ $id }}-judul" class="text-title-l" data-dialog-judul>{{ $judul }}</h2>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        <p class="mt-4 text-body-m text-on-surface-variant" data-dialog-pesan>{{ $pesan }}</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.tombol nada="hantu" data-dialog-batal>Batal</x-ui.tombol>
            <x-ui.tombol :nada="$nada === 'bahaya' ? 'bahaya' : 'hantu'" data-dialog-aksi>{{ $aksi }}</x-ui.tombol>
        </div>
    @endif
</dialog>
