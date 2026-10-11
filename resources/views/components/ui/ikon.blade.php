@props(['nama', 'ukuran' => 20])
{!! \App\Support\Ikon::render($nama, (int) $ukuran, (string) $attributes->get('class', '')) !!}
