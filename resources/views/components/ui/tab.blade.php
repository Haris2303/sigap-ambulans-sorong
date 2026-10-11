@props(['href', 'aktif' => false])
<a href="{{ $href }}" @if ($aktif) aria-current="page" @endif {{ $attributes->merge(['class' => 'lapisan-keadaan target-sentuh flex min-h-11 shrink-0 items-center whitespace-nowrap border-b-3 px-4 text-label-l transition-colors '.($aktif ? 'text-primary border-primary' : 'text-on-surface-variant border-transparent')]) }}>{{ $slot }}</a>
