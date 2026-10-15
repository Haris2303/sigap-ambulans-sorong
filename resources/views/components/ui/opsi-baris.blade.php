@props(['nama', 'nilai', 'judul', 'deskripsi' => null, 'ikon' => null, 'terpilih' => null])
{{-- Kartu pilihan arah B (DESIGN.md 6.4, x-ui.choice): radio tersembunyi, petak ikon opsional (dibungkus
     x-ui.opsi-kisi untuk kisi 3 kolom, dipakai untuk jenis layanan). Terpilih: batas dan cincin dalam primary,
     latar selected, petak ikon primary/on-primary. deskripsi tetap didukung (dipakai alur pesanan yang ada).
     Petak ikon 30px (size-7.5) dalam kisi tanpa deskripsi; 40px (size-10, docs/RETEMA_B.md 8.5) saat deskripsi
     tampil di baris (dipakai warga/pesanan/buat.blade.php), supaya proporsinya seimbang dengan baris yang lebih tinggi. --}}
@php
    $dipilih = old($nama) !== null ? (string) old($nama) === (string) $nilai : (bool) $terpilih;
    $kotakIkon = $deskripsi ? 'size-10' : 'size-7.5';
    $ukuranIkon = $deskripsi ? 21 : 18;
@endphp
<label class="{{ $dipilih ? '' : 'lapisan-keadaan' }} has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary flex min-h-14 cursor-pointer items-center gap-2.5 rounded-md border px-2.5 py-3 transition-colors {{ $dipilih ? 'border-primary bg-selected ring-1 ring-inset ring-primary text-on-surface' : 'border-outline text-on-surface' }}">
    <input type="radio" name="{{ $nama }}" value="{{ $nilai }}" @checked($dipilih) class="sr-only">
    @if ($ikon)
        <span class="inline-flex {{ $kotakIkon }} shrink-0 items-center justify-center rounded-sm {{ $dipilih ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant' }}">
            <x-ui.ikon :nama="$ikon" :ukuran="$ukuranIkon" />
        </span>
    @endif
    <span class="min-w-0">
        <span class="block text-label-l">{{ $judul }}</span>
        @if ($deskripsi)
            <span class="mt-0.5 block text-body-s text-on-surface-variant">{{ $deskripsi }}</span>
        @endif
    </span>
</label>
