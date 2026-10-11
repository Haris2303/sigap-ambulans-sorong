@props(['nama'])
{{-- Lingkaran berinisial; dekoratif, nama selalu ada di sebelahnya. --}}
<span aria-hidden="true" {{ $attributes->merge(['class' => 'inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-label-l text-on-primary-container']) }}>{{ \App\Support\Tampilan::inisial((string) $nama) }}</span>
