@props(['nada' => 'netral'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-sm px-2 py-0.5 text-caption '.\App\Support\Tampilan::kelasLembut($nada)]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $slot }}
</span>
