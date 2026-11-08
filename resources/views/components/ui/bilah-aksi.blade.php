{{-- Bilah aksi lekat di dasar layar pada ponsel: aksi utama halaman tetap terjangkau ibu jari tanpa menggulir. Di lg ke atas menyatu dengan isi.
     Letakkan sebagai anak TERAKHIR `panel` berpadding 24px: -mx-6 dan -mb-6 membuatnya selebar kartu. Satu tombol utama selebar layar di dalamnya. --}}
<div data-bilah-aksi {{ $attributes->merge(['class' => 'sticky bottom-0 z-30 -mx-6 -mb-6 mt-6 rounded-b-lg border-t border-outline-variant bg-surface-container-lowest px-6 py-4 shadow-level-1 lg:static lg:mx-0 lg:mb-0 lg:rounded-none lg:bg-transparent lg:px-0 lg:pb-0 lg:pt-5 lg:shadow-none']) }}>
    {{ $slot }}
</div>
