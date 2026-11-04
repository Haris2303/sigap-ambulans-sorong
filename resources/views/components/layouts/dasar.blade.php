@props(['judul' => null, 'peta' => false])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $judul ? $judul.' - SIGAP Ambulans' : 'SIGAP Ambulans' }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%231368ce'/%3E%3Cpath d='M13 7h6v6h6v6h-6v6h-6v-6H7v-6h6z' fill='%23fff'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($peta)
        @vite('resources/js/peta.js')
    @endif
</head>
<body>
    <a href="#isi" class="sr-only rounded-full bg-primary px-4 py-2 text-label-l text-on-primary focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50">Lewati ke isi</a>
    {{ $slot }}
    <x-ui.dialog />
    @stack('skrip')
</body>
</html>
