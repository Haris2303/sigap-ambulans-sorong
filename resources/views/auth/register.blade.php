<x-layouts.auth judul="Daftar">
    <h1 class="text-page-title-s">Daftar</h1>
    <p class="mb-6 mt-1 text-body-m text-on-surface-variant">Pendaftaran untuk Masyarakat yang akan memesan ambulans.</p>

    <form method="POST" action="{{ route('register.proses') }}" class="space-y-5">
        @csrf
        <x-ui.isian nama="name" label="Nama lengkap" :wajib="true" autocomplete="name" />
        <x-ui.isian nama="email" label="Email" tipe="email" :wajib="true" autocomplete="username" />
        <x-ui.isian nama="no_telepon" label="Nomor telepon" tipe="tel" bantuan="Dipakai sebagai kontak saat Anda memesan ambulans." :wajib="true" autocomplete="tel" inputmode="tel" />
        <x-ui.isian nama="password" label="Kata sandi" tipe="password" bantuan="Paling sedikit 8 karakter." :wajib="true" autocomplete="new-password" />
        <x-ui.isian nama="password_confirmation" label="Ulangi kata sandi" tipe="password" :wajib="true" autocomplete="new-password" />
        <x-ui.tombol tipe="submit" :blok="true">Daftar</x-ui.tombol>
    </form>

    <p class="mt-6 text-center text-label-l">Sudah punya akun? <a href="{{ route('login') }}" class="text-primary">Masuk</a></p>
</x-layouts.auth>
