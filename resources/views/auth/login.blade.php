<x-layouts.auth judul="Masuk">
    <x-ui.judul-halaman judul="Masuk" deskripsi="Gunakan akun yang terdaftar di SIGAP Ambulans." />
    <x-ui.panel>
        <form method="POST" action="{{ route('login.proses') }}" class="space-y-5">
            @csrf
            <x-ui.isian nama="email" label="Email" tipe="email" :wajib="true" autocomplete="username" />
            <x-ui.isian nama="password" label="Kata sandi" tipe="password" :wajib="true" autocomplete="current-password" />
            <x-ui.centang nama="ingat" label="Ingat saya di perangkat ini" />
            <x-ui.tombol tipe="submit" :blok="true">Masuk</x-ui.tombol>
        </form>
    </x-ui.panel>
    <p class="mt-4 text-body-sm text-on-surface-muted">Belum punya akun? <a href="{{ route('register') }}" class="text-primary underline underline-offset-2 hover:text-primary-hover">Daftar sebagai Masyarakat</a></p>
</x-layouts.auth>
