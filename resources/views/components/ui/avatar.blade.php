@props(['nama', 'size' => 'md'])
{{-- Lingkaran berinisial (DESIGN.md 6.7): md 40px, sm 36px, teks gaya chip. Warna dari hash nama (tidak membawa makna);
     dekoratif (aria-hidden), nama selalu ada di sebelahnya. --}}
@php
    $ukuran = match ($size) {
        'md' => 'size-10',
        'sm' => 'size-9',
        default => throw new \InvalidArgumentException('x-ui.avatar: size harus md atau sm, bukan "'.$size.'".'),
    };
@endphp
<span aria-hidden="true" {{ $attributes->merge(['class' => 'inline-flex '.$ukuran.' shrink-0 items-center justify-center rounded-full text-chip '.\App\Support\Tampilan::warnaAvatar((string) $nama)]) }}>{{ \App\Support\Tampilan::inisial((string) $nama) }}</span>
