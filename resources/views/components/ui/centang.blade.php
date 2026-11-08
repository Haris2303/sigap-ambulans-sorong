@props(['nama', 'label', 'nilai' => false])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $dicentang = (bool) old($nama, $nilai);
@endphp
<div>
    <input type="hidden" name="{{ $nama }}" value="0">
    <label for="{{ $id }}" class="lapisan-keadaan -mx-3 flex min-h-12 items-center gap-3 rounded-md px-3 text-body-l text-on-surface">
        <input id="{{ $id }}" name="{{ $nama }}" type="checkbox" value="1" @checked($dicentang)
            {{ $attributes->merge(['class' => 'size-4.5 shrink-0 accent-primary']) }}>
        <span>{{ $label }}</span>
    </label>
</div>
