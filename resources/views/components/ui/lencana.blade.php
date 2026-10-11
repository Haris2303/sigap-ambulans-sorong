@props(['nada' => 'netral'])
<span {{ $attributes->merge(['class' => 'inline-flex min-h-6 items-center gap-1.5 rounded-full px-2.5 text-label-m '.\App\Support\Tampilan::kelasLembut($nada)]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $slot }}
</span>
