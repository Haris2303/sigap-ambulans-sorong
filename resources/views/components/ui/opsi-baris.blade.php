@props(['nama', 'nilai', 'judul', 'deskripsi' => null, 'ikon' => null, 'terpilih' => null])
{{-- Kartu pilihan arah B (DESIGN.md 6.4, x-ui.choice): radio tersembunyi, petak ikon opsional (dibungkus
     x-ui.opsi-kisi untuk kisi 3 kolom, dipakai untuk jenis layanan). Terpilih: batas dan cincin dalam primary,
     latar selected, petak ikon primary/on-primary. deskripsi tetap didukung (dipakai alur pesanan yang ada).
     Petak ikon 30px (size-7.5) dalam kisi tanpa deskripsi; 40px (size-10, docs/RETEMA_B.md 8.5) saat deskripsi
     tampil di baris (dipakai warga/pesanan/buat.blade.php), supaya proporsinya seimbang dengan baris yang lebih tinggi.
     Gaya terpilih lewat has-[:checked]/group-has-[:checked], bukan kelas kondisional PHP: tanpa ini klik radio
     mengubah status tapi tampilan tetap diam sampai muat ulang halaman (ditemukan lewat uji browser nyata).
     lapisan-keadaan tetap kondisional ke $dipilih (dihitung sekali saat render): baris yang sudah terpilih
     saat halaman dimuat tidak diberi efek hover/tekan tambahan di atas latar selected-nya. --}}
@php
    $dipilih = old($nama) !== null ? (string) old($nama) === (string) $nilai : (bool) $terpilih;
    $kotakIkon = $deskripsi ? 'size-10' : 'size-7.5';
    $ukuranIkon = $deskripsi ? 21 : 18;
@endphp
<label class="{{ $dipilih ? '' : 'lapisan-keadaan' }} group has-[:checked]:border-primary has-[:checked]:bg-selected has-[:checked]:ring-1 has-[:checked]:ring-inset has-[:checked]:ring-primary has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-primary flex min-h-14 cursor-pointer items-center gap-2.5 rounded-md border border-outline px-2.5 py-3 text-on-surface transition-colors">
    <input type="radio" name="{{ $nama }}" value="{{ $nilai }}" @checked($dipilih) class="sr-only">
    @if ($ikon)
        <span class="inline-flex {{ $kotakIkon }} shrink-0 items-center justify-center rounded-sm bg-surface-container text-on-surface-variant transition-colors group-has-[:checked]:bg-primary group-has-[:checked]:text-on-primary">
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
