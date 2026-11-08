@props(['pesanan'])
{{-- Lencana waktu tunggu: lama pesanan menunggu penugasan (status Menunggu dan Terverifikasi), bernada netral, peringatan, atau bahaya menurut ambang di config/sigap.php.
     Server mengirim detik sejak dibuat (data-tunggu-detik); resources/js/waktu-tunggu.js menghitung maju dari situ, tanpa bergantung pada jam perangkat. Status lain: tidak tampil. --}}
@if ($pesanan->status->menungguPenugasan() && $pesanan->created_at)
    @php
        $detik = \App\Support\WaktuTunggu::detik($pesanan->created_at);
        $menit = intdiv($detik, 60);
        $ambang = \App\Support\WaktuTunggu::ambang();
    @endphp
    <span data-waktu-tunggu data-tunggu-detik="{{ $detik }}" data-ambang-peringatan="{{ $ambang['peringatan'] }}" data-ambang-bahaya="{{ $ambang['bahaya'] }}"
        data-nada="{{ \App\Support\WaktuTunggu::nada($menit, $ambang) }}"
        {{ $attributes->merge(['class' => 'inline-flex min-h-6 items-center gap-1.5 rounded-full px-2.5 text-label-m tabular-nums '.\App\Support\Tampilan::WAKTU_TUNGGU]) }}>
        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
        <span data-teks>{{ \App\Support\WaktuTunggu::teks($menit) }}</span>
    </span>
@endif
