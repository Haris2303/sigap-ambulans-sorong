@props(['bingkai' => true])
{{-- Daftar definisi. bingkai=true: kartu berbayang; false: tanpa gaya kartu, untuk di dalam kartu lain (kartu berbayang tidak bersarang). --}}
<dl {{ $attributes->merge(['class' => 'divide-y divide-outline-variant'.($bingkai ? ' '.\App\Support\Tampilan::KARTU.' overflow-hidden' : '')]) }}>
    {{ $slot }}
</dl>
