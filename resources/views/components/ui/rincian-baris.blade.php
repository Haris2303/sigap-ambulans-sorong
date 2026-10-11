@props(['label'])
{{-- Daftar info arah B (DESIGN.md 6.12): label label-m on-surface-variant, nilai body-m. --}}
<div class="grid gap-1 px-6 py-3 sm:grid-cols-3 sm:gap-4">
    <dt class="text-label-m text-on-surface-variant">{{ $label }}</dt>
    <dd class="min-w-0 break-words text-body-m sm:col-span-2">{{ $slot }}</dd>
</div>
