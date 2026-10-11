@props(['nilai' => null, 'relatif' => false, 'format' => 'd M Y H:i'])
@php
    $waktu = $nilai === null || $nilai === '' ? null : \Illuminate\Support\Carbon::parse($nilai)->timezone(config('app.timezone'));
@endphp
@if ($waktu)
    <time datetime="{{ $waktu->toIso8601String() }}" {{ $attributes->merge(['class' => 'tabular-nums']) }}>{{ $waktu->translatedFormat($format) }}@if ($relatif) ({{ $waktu->diffForHumans() }})@endif</time>
@else
    <span {{ $attributes }}>-</span>
@endif
