@props(['judul' => null, 'peta' => false])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $judul ? $judul.' - SIGAP Ambulans' : 'SIGAP Ambulans' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($peta)
        @vite('resources/js/peta.js')
    @endif
</head>
<body>
    <a href="#isi" class="sr-only rounded-md bg-surface px-4 py-2 text-label text-primary focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50">Lewati ke isi</a>
    {{ $slot }}
    <x-ui.dialog />
    @stack('skrip')
</body>
</html>
