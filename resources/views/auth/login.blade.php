<x-layouts.auth judul="Masuk">
    <h1 class="text-page-title-s">Masuk</h1>
    <p class="mb-6 mt-1 text-body-m text-on-surface-variant">Gunakan akun yang terdaftar di SIGAP Ambulans.</p>

    <form method="POST" action="{{ route('login.proses') }}" class="space-y-5">
        @csrf
        <x-ui.isian nama="email" label="Email" tipe="email" :wajib="true" autocomplete="username" />
        <x-ui.isian nama="password" label="Kata sandi" tipe="password" :wajib="true" autocomplete="current-password" />
        <x-ui.centang nama="ingat" label="Ingat saya di perangkat ini" />
        <x-ui.tombol tipe="submit" :blok="true">Masuk</x-ui.tombol>
    </form>

    <p class="mt-6 text-center text-label-l">Belum punya akun? <a href="{{ route('register') }}" class="text-primary">Daftar sebagai Masyarakat</a></p>
</x-layouts.auth>
