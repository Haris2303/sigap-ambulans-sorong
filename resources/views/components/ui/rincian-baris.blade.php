@props(['label'])
<div class="grid gap-1 px-4 py-3 text-body-m sm:grid-cols-3 sm:gap-4">
    <dt class="text-on-surface-variant">{{ $label }}</dt>
    <dd class="min-w-0 break-words sm:col-span-2">{{ $slot }}</dd>
</div>
