@props(['nama', 'label', 'nilai' => false])
@php
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $nama);
    $dicentang = (bool) old($nama, $nilai);
@endphp
<div>
    <input type="hidden" name="{{ $nama }}" value="0">
    <label for="{{ $id }}" class="flex min-h-touch items-center gap-3 text-body text-on-surface">
        <input id="{{ $id }}" name="{{ $nama }}" type="checkbox" value="1" @checked($dicentang)
            {{ $attributes->merge(['class' => 'size-5 rounded-sm accent-primary']) }}>
        <span>{{ $label }}</span>
    </label>
</div>
