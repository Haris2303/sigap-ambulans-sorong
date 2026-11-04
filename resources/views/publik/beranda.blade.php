{{--
    Pengecualian sempit dari DESIGN.md, hanya untuk berkas ini (keputusan pemilik proyek, lihat
    docs/laporan/ASUMSI_DAN_KEPUTUSAN.md): gaya dan markup sendiri (<style>, <button> murni-JS,
    efek hover) di luar kit x-ui.* dan token Tailwind, supaya beranda publik bisa punya tampilan
    "web profile" yang lebih bebas. Warna tetap lewat var(--color-...) dari design-tokens.css,
    bukan heksadesimal baru, supaya tetap satu identitas dengan halaman lain.
--}}
@php
    $kontak = \App\Support\KontakDarurat::data();
    $langkahDemo = [
        ['judul' => 'Ajukan pesanan', 'teks' => 'Masyarakat mengisi pesanan dan lokasi jemput.', 'status' => \App\Enums\PesananStatus::Menunggu->label()],
        ['judul' => 'Dinkes memverifikasi', 'teks' => 'Dinkes memverifikasi pesanan.', 'status' => \App\Enums\PesananStatus::Terverifikasi->label()],
        ['judul' => 'Ambulans ditugaskan', 'teks' => 'Dinkes menugaskan ambulans dan sopir.', 'status' => \App\Enums\PesananStatus::Ditugaskan->label()],
        ['judul' => 'Pantau sampai tiba', 'teks' => 'Masyarakat memantau perjalanan ambulans sampai tiba.', 'status' => \App\Enums\PesananStatus::DalamPerjalanan->label()],
    ];
@endphp
<x-layouts.publik judul="Beranda" :polos="true">
<style>
:root{
  /* Alias ke token DESIGN.md (resources/css/design-tokens.css) - bukan warna baru. */
  --laut: var(--color-secondary);
  --laut-gelap: color-mix(in srgb, var(--color-secondary) 78%, #000);
  --teal-700: var(--color-primary);
  --teal-800: color-mix(in srgb, var(--color-primary) 82%, #000);
  --mist: var(--color-primary-container);
  --paper: var(--color-surface);
  --ink: var(--color-on-surface);
  --ink-soft: var(--color-on-surface-variant);
  --line: var(--color-outline-variant);
  --line-kuat: var(--color-outline);
  --bata: var(--color-error);
  --putih: var(--color-on-secondary);
  --font: var(--font-sans);
}
.sg *,.sg *::before,.sg *::after{box-sizing:border-box}
.sg [hidden]{display:none!important}
.sg{font:400 1.0625rem/1.6 var(--font);color:var(--ink);background:var(--paper)}
.sg img,.sg svg{max-width:100%}
.sg a{color:var(--teal-700)}
.sg :focus-visible{outline:3px solid var(--teal-700);outline-offset:3px;border-radius:4px}
.sg .sg-gelap :focus-visible,.sg .sg-strip :focus-visible,.sg .sg-kaki :focus-visible{outline-color:#fff}

.sg .sg-wadah{width:min(100% - 40px,1080px);margin-inline:auto}
.sg .sr-only{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0}

.sg h1,.sg h2,.sg h3{font-family:var(--font);color:var(--laut);margin:0}
.sg h2{font-size:clamp(1.625rem,4.6vw,2.25rem);line-height:1.15;letter-spacing:-.02em;font-weight:800}
.sg h3{font-size:1.1875rem;line-height:1.3;font-weight:700}
.sg p{margin:0}
.sg .sg-lead{font-size:1.125rem;color:var(--ink-soft);max-width:46ch;margin-top:12px}

.sg .sg-tombol{display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:56px;padding:0 1.5rem;border-radius:var(--radius-md);border:2px solid transparent;font:700 1.0625rem/1.2 var(--font);text-decoration:none;text-align:center;cursor:pointer;transition:background-color .15s,transform .1s}
.sg .sg-tombol:active{transform:translateY(1px)}
.sg .sg-tombol svg{flex:none}
.sg .sg-tombol-utama{background:var(--teal-700);color:var(--putih)}
.sg .sg-tombol-utama:hover{background:var(--teal-800)}
.sg .sg-tombol-garis{background:var(--color-surface-container-lowest);color:var(--teal-700);border-color:var(--teal-700)}
.sg .sg-tombol-garis:hover{background:var(--mist)}
.sg .sg-tombol-terang{background:var(--color-surface-container-lowest);color:var(--laut)}
.sg .sg-tombol-terang:hover{background:var(--mist)}
.sg .sg-tombol.sg-kecil{min-height:48px;padding:0 1.25rem;font-size:1rem}
.sg .sg-aksi{display:grid;gap:12px}
@media (min-width:640px){.sg .sg-aksi{grid-template-columns:repeat(2,max-content)}}

.sg .sg-strip{background:var(--laut);color:var(--putih);font-size:.9375rem}
.sg .sg-strip .sg-wadah{display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:2px 10px;padding:10px 0;text-align:center}
.sg .sg-strip a{color:var(--putih);font-weight:700}

.sg .sg-kepala{background:var(--color-surface-container-lowest);border-bottom:1px solid var(--line)}
@media (min-width:900px){.sg .sg-kepala{position:sticky;top:0;z-index:20}}
.sg .sg-kepala .sg-wadah{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:68px}
.sg .sg-merek{display:flex;align-items:center;gap:12px;color:var(--laut);text-decoration:none}
.sg .sg-merek-ikon{display:inline-flex;width:40px;height:40px;flex:none;align-items:center;justify-content:center;border-radius:var(--radius-sm);background:var(--color-primary);color:var(--color-on-primary)}
.sg .sg-merek strong{display:block;font-size:1.125rem;line-height:1.15;font-weight:800;letter-spacing:-.01em}
.sg .sg-merek small{display:block;font-size:.8125rem;line-height:1.3;color:var(--ink-soft)}
.sg .sg-menu{display:flex;align-items:center;gap:4px}
.sg .sg-menu a{display:inline-flex;align-items:center;min-height:44px;padding:0 12px;border-radius:var(--radius-sm);font-weight:600;color:var(--laut);text-decoration:none}
.sg .sg-menu a:hover{background:var(--mist)}
.sg .sg-menu .sg-tambahan{display:none}
@media (min-width:640px){.sg .sg-menu .sg-tambahan{display:inline-flex}}
.sg .sg-menu a.sg-pesan-cepat{display:none;background:var(--teal-700);color:var(--putih);padding:0 18px;margin-left:8px;font-weight:700}
.sg .sg-menu a.sg-pesan-cepat:hover{background:var(--teal-800)}
@media (min-width:900px){.sg .sg-menu a.sg-pesan-cepat{display:inline-flex}}

.sg .sg-flash{padding-top:16px}
.sg .sg-hero{padding:32px 0 44px}
.sg .sg-hero .sg-wadah{display:grid;gap:32px}
.sg .sg-hero h1{font-size:clamp(2.1875rem,8vw,3.375rem);line-height:1.06;letter-spacing:-.03em;font-weight:800}
.sg .sg-hero .sg-lead{font-size:1.1875rem;color:var(--ink);margin-top:16px}
.sg .sg-hero .sg-aksi{margin-top:28px}
.sg .sg-petunjuk{margin-top:16px;font-size:.9375rem;color:var(--ink-soft);max-width:44ch}
.sg .sg-peta{margin:0;max-width:440px}
.sg .sg-peta svg{display:block;width:100%;height:auto;border-radius:var(--radius-xl);border:1px solid var(--line)}
.sg .sg-peta figcaption{margin-top:8px;font-size:.875rem;color:var(--ink-soft)}
@media (min-width:900px){
  .sg .sg-hero{padding:72px 0 80px}
  .sg .sg-hero .sg-wadah{grid-template-columns:1.1fr .9fr;align-items:center;gap:64px}
  .sg .sg-peta{justify-self:end;max-width:none;width:100%}
}
.sg .sg-dot{opacity:0}
@supports (offset-path:path("M0 0")){
  .sg .sg-dot{
    opacity:1;
    offset-path:path("M64 200 C130 200 110 120 180 128 S250 140 290 70");
    offset-rotate:0deg;
    animation:sg-jalan 9s ease-in-out infinite;
  }
}
@keyframes sg-jalan{0%{offset-distance:0%}80%,100%{offset-distance:100%}}

.sg .sg-percaya{background:var(--color-surface-container-lowest);border-block:1px solid var(--line)}
.sg .sg-percaya .sg-wadah{display:grid;gap:20px;padding:24px 0}
.sg .sg-percaya-teks{display:flex;gap:12px;align-items:flex-start;max-width:46ch}
.sg .sg-percaya-teks svg{flex:none;margin-top:2px}
.sg .sg-percaya-ikon{color:var(--teal-700)}
.sg .sg-percaya-teks p{font-weight:600;line-height:1.5}
.sg .sg-percaya-teks small{display:block;margin-top:2px;font-weight:400;font-size:.9375rem;color:var(--ink-soft)}
.sg .sg-statistik{display:flex;flex-wrap:wrap;gap:12px 36px;margin:0}
.sg .sg-stat{display:flex;flex-direction:column-reverse}
.sg .sg-stat dd{margin:0;font-size:2rem;line-height:1.1;font-weight:800;letter-spacing:-.02em;color:var(--teal-700)}
.sg .sg-stat dt{font-size:.9375rem;color:var(--ink-soft)}
@media (min-width:900px){
  .sg .sg-percaya .sg-wadah{grid-template-columns:1fr auto;align-items:center;gap:48px;padding:28px 0}
}

.sg .sg-bagian{padding:56px 0}
.sg .sg-bagian-pucat{background:var(--mist)}
@media (min-width:900px){.sg .sg-bagian{padding:88px 0}}

.sg .sg-split{display:grid;gap:32px}
@media (min-width:900px){.sg .sg-split{grid-template-columns:5fr 7fr;gap:72px;align-items:start}}
.sg .sg-langkah{list-style:none;margin:0;padding:0;counter-reset:langkah}
.sg .sg-langkah li{position:relative;counter-increment:langkah;padding:0 0 32px 64px}
.sg .sg-langkah li:last-child{padding-bottom:0}
.sg .sg-langkah li::before{content:counter(langkah);position:absolute;left:0;top:0;width:44px;height:44px;border-radius:50%;background:var(--teal-700);color:var(--putih);font-weight:800;font-size:1.125rem;display:grid;place-items:center}
.sg .sg-langkah li:not(:last-child)::after{content:"";position:absolute;left:21px;top:54px;bottom:8px;border-left:3px dotted var(--line-kuat)}
.sg .sg-langkah h3{padding-top:8px}
.sg .sg-langkah p{margin-top:4px;color:var(--ink-soft);max-width:44ch}
.sg.sg-demo-aktif .sg-langkah{display:none}

.sg .sg-petunjuk-js{display:none;margin-top:10px;font-size:.9375rem;color:var(--ink-soft)}
.sg.sg-demo-aktif .sg-petunjuk-js{display:block}
.sg .sg-stepper{display:grid;grid-template-columns:repeat(4,1fr);gap:4px;margin-top:24px;position:relative}
.sg .sg-stepper:empty{display:none}
.sg .sg-stepper::before{content:"";position:absolute;top:22px;left:12.5%;right:12.5%;border-top:3px dotted var(--line-kuat)}
.sg .sg-st{position:relative;display:flex;flex-direction:column;align-items:center;gap:8px;min-height:44px;padding:0 2px 6px;background:none;border:0;border-radius:var(--radius-sm);font:inherit;color:var(--ink-soft);text-align:center;cursor:pointer}
.sg .sg-st .sg-no{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;background:var(--color-surface-container-lowest);border:2px solid var(--teal-700);color:var(--teal-700);font-weight:800;font-size:1.125rem;transition:background-color .2s,color .2s,box-shadow .2s}
.sg .sg-st .sg-nama{font-size:.8125rem;line-height:1.25;font-weight:600}
.sg .sg-st:hover .sg-no{box-shadow:0 0 0 4px color-mix(in srgb, var(--teal-700) 16%, transparent)}
.sg .sg-st.sg-selesai .sg-no{background:var(--teal-700);color:var(--putih)}
.sg .sg-st[aria-current="step"] .sg-no{background:var(--teal-700);color:var(--putih);box-shadow:0 0 0 5px color-mix(in srgb, var(--teal-700) 24%, transparent)}
.sg .sg-st[aria-current="step"] .sg-nama{color:var(--laut);font-weight:800}
@media (min-width:900px){
  .sg .sg-stepper{grid-template-columns:1fr;gap:6px;margin-top:32px}
  .sg .sg-stepper::before{display:none}
  .sg .sg-st{flex-direction:row;gap:14px;padding:10px 14px 10px 10px;border-radius:var(--radius-lg);text-align:left}
  .sg .sg-st .sg-nama{font-size:1.0625rem}
  .sg .sg-st[aria-current="step"]{background:var(--color-surface-container-lowest);box-shadow:0 0 0 1px var(--line)}
}

.sg .sg-demo{margin-top:24px;padding:20px;background:var(--color-surface-container-lowest);border:1px solid var(--line);border-radius:var(--radius-lg)}
.sg .sg-demo-kepala{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px 12px}
.sg .sg-demo-judul{font-size:.9375rem;font-weight:600;color:var(--ink-soft)}
.sg .sg-chip{display:inline-flex;align-items:center;gap:8px;min-height:32px;padding:0 14px;border-radius:var(--radius-full);background:var(--mist);color:var(--laut);font-weight:700;font-size:.9375rem}
.sg .sg-chip::before{content:"";width:8px;height:8px;border-radius:50%;background:var(--teal-700)}
.sg .sg-chip.sg-tiba{background:var(--teal-700);color:var(--putih)}
.sg .sg-chip.sg-tiba::before{background:var(--putih)}
.sg .sg-demo-teks{margin-top:14px;min-height:4.8em;color:var(--ink)}
.sg .sg-demo-isi{display:grid;gap:20px;margin-top:6px}
.sg .sg-demo-peta{display:block;width:100%;height:auto;border-radius:var(--radius-lg);border:1px solid var(--line)}
.sg .sg-demo-kaki{display:flex;flex-wrap:wrap;align-items:center;gap:12px 16px;margin-top:20px}
.sg .sg-catatan{flex:1 1 220px;font-size:.875rem;line-height:1.45;color:var(--ink-soft)}
@media (min-width:900px){
  .sg .sg-demo{margin-top:0;padding:28px}
  .sg .sg-demo-teks{min-height:3.2em}
  .sg .sg-demo-isi{grid-template-columns:1.25fr 1fr;align-items:start;gap:28px}
}

.sg .sg-f,.sg .sg-kendaraan{opacity:0;transition:opacity .4s}
.sg .sg-f.sg-tampak,.sg .sg-kendaraan.sg-tampak{opacity:1}
.sg #sg-m-rute{transition:stroke-dashoffset 1s ease}
.sg .sg-halo{fill:var(--teal-700);opacity:.18;transition:opacity .4s}
.sg .sg-tiba-peta .sg-halo{opacity:.4}

.sg .sg-linimasa{list-style:none;margin:0;padding:0}
.sg .sg-linimasa li{position:relative;padding:0 0 14px 28px;font-size:.9375rem;line-height:1.4;color:var(--ink-soft);transition:opacity .3s,transform .3s}
.sg .sg-linimasa li:last-child{padding-bottom:0}
.sg .sg-linimasa li::before{content:"";position:absolute;left:0;top:3px;width:14px;height:14px;border-radius:50%;border:2px solid var(--teal-700);background:var(--color-surface-container-lowest)}
.sg .sg-linimasa li::after{content:"";position:absolute;left:6px;top:20px;bottom:-1px;border-left:2px solid var(--line-kuat)}
.sg .sg-linimasa li:last-child::after,.sg .sg-linimasa li.sg-kini::after{display:none}
.sg .sg-linimasa li.sg-selesai::before{background:var(--teal-700)}
.sg .sg-linimasa li.sg-kini{color:var(--ink);font-weight:700}
.sg .sg-linimasa li.sg-kini::before{background:var(--teal-700);box-shadow:0 0 0 4px color-mix(in srgb, var(--teal-700) 24%, transparent)}
.sg .sg-linimasa li.sg-belum{opacity:0;transform:translateY(4px)}

.sg .sg-layanan{list-style:none;margin:32px 0 0;padding:0}
.sg .sg-layanan li{display:grid;grid-template-columns:56px 1fr;gap:2px 16px;align-content:start;padding:24px 0;border-top:1px solid var(--line-kuat)}
.sg .sg-layanan li:last-child{border-bottom:1px solid var(--line-kuat)}
.sg .sg-layanan .sg-ikon{grid-row:span 3;width:56px;height:56px;border-radius:50%;background:var(--color-surface-container-lowest);display:grid;place-items:center;color:var(--teal-700)}
.sg .sg-layanan .sg-darurat .sg-ikon{color:var(--bata)}
.sg .sg-layanan p{color:var(--ink-soft);max-width:36ch}
.sg .sg-layanan .sg-pilih{justify-self:start;margin-top:14px}
@media (min-width:900px){
  .sg .sg-layanan{display:grid;grid-template-columns:repeat(3,1fr);gap:0 48px;margin-top:40px}
  .sg .sg-layanan li{grid-template-columns:1fr;grid-template-rows:auto auto 1fr auto;gap:12px;padding:28px 0 0;border-top:3px solid var(--line-kuat)}
  .sg .sg-layanan li:last-child{border-bottom:0}
  .sg .sg-layanan .sg-darurat{border-top-color:var(--bata)}
  .sg .sg-layanan .sg-ikon{grid-row:auto}
  .sg .sg-layanan .sg-pilih{margin-top:6px;align-self:end}
}

.sg .sg-band{display:grid;gap:20px;padding:28px;background:var(--color-surface-container-lowest);border:1px solid var(--line);border-radius:var(--radius-lg)}
.sg .sg-band p{color:var(--ink-soft);max-width:58ch;margin-top:10px}
@media (min-width:800px){.sg .sg-band{grid-template-columns:1fr auto;align-items:center;gap:40px;padding:40px 44px}}

.sg .sg-manfaat{list-style:none;margin:32px 0 0;padding:0;display:grid;gap:28px}
.sg .sg-manfaat li{display:grid;grid-template-columns:32px 1fr;gap:14px}
.sg .sg-cek{width:32px;height:32px;border-radius:50%;background:var(--teal-700);display:grid;place-items:center;color:var(--putih)}
.sg .sg-manfaat p{margin-top:4px;color:var(--ink-soft);max-width:40ch}
@media (min-width:720px){.sg .sg-manfaat{grid-template-columns:1fr 1fr;gap:36px 56px;margin-top:40px}}

.sg .sg-gelap{background:var(--laut);color:#E6F1F2}
.sg .sg-gelap h2{color:var(--putih)}
.sg .sg-cadangan .sg-wadah{display:grid;gap:28px}
.sg .sg-cadangan .sg-judul{display:flex;gap:16px;align-items:flex-start}
.sg .sg-badge-telp{flex:none;width:56px;height:56px;border-radius:50%;background:var(--bata);display:grid;place-items:center;color:var(--putih)}
.sg .sg-cadangan p{margin-top:10px;max-width:50ch}
.sg .sg-tel-aksi{display:flex;flex-direction:column;gap:10px}
.sg .sg-tombol-besar{min-height:64px;font-size:1.25rem;width:100%}
.sg .sg-salin{display:none;background:transparent;color:var(--putih);border-color:color-mix(in srgb, var(--putih) 70%, transparent)}
.sg .sg-salin:hover{background:color-mix(in srgb, var(--putih) 12%, transparent)}
@media (hover:hover) and (pointer:fine){.sg.sg-js .sg-salin{display:inline-flex}}
@media (min-width:900px){
  .sg .sg-cadangan .sg-wadah{grid-template-columns:1fr auto;align-items:center;gap:56px}
  .sg .sg-tombol-besar{width:auto;padding:0 2.25rem}
}

.sg .sg-cta .sg-aksi{margin-top:28px}

.sg .sg-kaki{background:var(--laut-gelap);color:#BBD3D6;padding:36px 0;font-size:.9375rem}
.sg .sg-kaki .sg-wadah{display:grid;gap:16px}
.sg .sg-kaki .sg-merek{color:var(--putih)}
.sg .sg-kaki .sg-merek small{color:#BBD3D6}
.sg .sg-kaki nav{display:flex;flex-wrap:wrap;gap:4px 20px}
.sg .sg-kaki a{color:var(--putih)}
.sg .sg-kaki .sg-hak{font-size:.875rem}

.sg .sg-bar{position:fixed;left:0;right:0;bottom:0;z-index:30;display:flex;gap:10px;padding:10px 12px calc(10px + env(safe-area-inset-bottom));background:color-mix(in srgb, var(--color-surface-container-lowest) 97%, transparent);border-top:1px solid var(--line);box-shadow:0 -4px 16px color-mix(in srgb, var(--laut) 8%, transparent);transform:translateY(110%);visibility:hidden;transition:transform .25s ease,visibility 0s linear .25s}
.sg .sg-bar.sg-tampil{transform:none;visibility:visible;transition:transform .25s ease}
.sg .sg-bar .sg-tombol{min-height:52px}
.sg .sg-bar .sg-pesan{flex:1}
.sg .sg-bar .sg-telp{flex:none;padding:0 1.1rem}
@media (min-width:900px){.sg .sg-bar{display:none}}

@media (prefers-reduced-motion:reduce){
  .sg .sg-dot{animation:none;offset-distance:55%}
  .sg *,.sg *::before,.sg *::after{transition-duration:.01ms!important;animation-duration:.01ms!important}
}
</style>

<div class="sg">
  @if ($kontak)
    <div class="sg-strip">
      <div class="sg-wadah">
        <span>Aplikasi tidak bisa dibuka? Telepon {{ $kontak['nama'] }}:</span>
        <a href="tel:{{ $kontak['tel'] }}">{{ $kontak['nomor'] }}</a>
      </div>
    </div>
  @endif

  <header class="sg-kepala">
    <div class="sg-wadah">
      <a class="sg-merek" href="{{ route('beranda') }}" aria-label="SIGAP Ambulans, Dinas Kesehatan Kota Sorong, beranda">
        <span class="sg-merek-ikon" aria-hidden="true"><x-ui.ikon nama="plus" :ukuran="22" /></span>
        <span><strong>SIGAP Ambulans</strong><small>Dinas Kesehatan Kota Sorong</small></span>
      </a>
      <nav class="sg-menu" aria-label="Menu utama">
        <a class="sg-tambahan" href="{{ route('faskes.index') }}">Info Faskes</a>
        @auth
          <a href="{{ route(auth()->user()->role->ruteBeranda()) }}">Ke dasbor</a>
        @else
          <a href="{{ route('login') }}">Masuk</a>
        @endauth
        <a class="sg-pesan-cepat" href="{{ $aksiUtama['url'] }}">{{ $aksiUtama['label'] }}</a>
      </nav>
    </div>
  </header>

  <main id="isi">
    <div class="sg-wadah sg-flash">
      <x-ui.flash />
    </div>

    {{-- 1. Hero --}}
    <section class="sg-hero" aria-labelledby="sg-judul-hero">
      <div class="sg-wadah">
        <div>
          <h1 id="sg-judul-hero">Pesan ambulans Dinas Kesehatan Kota Sorong.</h1>
          <p class="sg-lead">SIGAP Ambulans menerima pesanan ambulans dari Masyarakat untuk keadaan darurat, rujukan, dan persalinan. Dinas Kesehatan memverifikasi setiap pesanan, lalu menugaskan ambulans dan sopir dari Fasilitas Kesehatan terdekat.</p>
          <div class="sg-aksi">
            <a class="sg-tombol sg-tombol-utama" href="{{ $aksiUtama['url'] }}">{{ $aksiUtama['label'] }}</a>
            <a class="sg-tombol sg-tombol-garis" href="{{ route('faskes.index') }}">Info Faskes</a>
          </div>
          <p class="sg-petunjuk">Anda akan diminta masuk dulu sebelum memesan bila belum masuk. Info Faskes bisa dilihat tanpa akun.</p>
        </div>

        <figure class="sg-peta">
          <svg viewBox="0 0 360 260" aria-hidden="true" focusable="false">
            <rect width="360" height="260" fill="var(--mist)"/>
            <path d="M60 0v260M120 0v260M180 0v260M240 0v260M300 0v260M0 50h360M0 100h360M0 150h360M0 200h360" stroke="var(--line)" stroke-width="1" fill="none"/>
            <path d="M-10 232C60 214 110 250 190 226S310 196 370 214" fill="none" stroke="var(--color-surface-container-lowest)" stroke-width="16"/>
            <path d="M236-10C228 60 262 120 244 270" fill="none" stroke="var(--color-surface-container-lowest)" stroke-width="12"/>
            <path d="M64 200C130 200 110 120 180 128S250 140 290 70" fill="none" stroke="var(--teal-700)" stroke-width="4" stroke-linecap="round" stroke-dasharray="1 10"/>
            <rect x="48" y="184" width="32" height="32" rx="8" fill="var(--teal-700)"/>
            <path d="M64 191v18M55 200h18" stroke="var(--putih)" stroke-width="4" stroke-linecap="round"/>
            <circle cx="290" cy="70" r="22" fill="var(--teal-700)" opacity=".18"/>
            <circle cx="290" cy="70" r="10" fill="var(--laut)"/>
            <circle cx="290" cy="70" r="3.5" fill="var(--putih)"/>
            <text x="48" y="240" font-size="13" font-weight="600" fill="var(--laut)" font-family="system-ui,sans-serif">Faskes terdekat</text>
            <text x="290" y="36" font-size="13" font-weight="600" fill="var(--laut)" text-anchor="middle" font-family="system-ui,sans-serif">Lokasi jemput</text>
            <circle class="sg-dot" cx="0" cy="0" r="8" fill="var(--putih)" stroke="var(--teal-700)" stroke-width="4"/>
          </svg>
          <figcaption>Ilustrasi pemantauan lokasi sopir</figcaption>
        </figure>
      </div>
    </section>

    {{-- 2. Baris kepercayaan --}}
    <section class="sg-percaya" aria-label="Tentang layanan">
      <div class="sg-wadah">
        <div class="sg-percaya-teks">
          <x-ui.ikon nama="circle-check" :ukuran="26" class="sg-percaya-ikon" />
          <p>Layanan resmi Dinas Kesehatan Kota Sorong
            <small>Terhubung dengan fasilitas kesehatan (Faskes): rumah sakit, Puskesmas, dan klinik di kota ini.</small>
          </p>
        </div>
        <dl class="sg-statistik">
          <div class="sg-stat"><dt>Faskes terhubung</dt><dd>{{ $statistik['faskes'] }}</dd></div>
          <div class="sg-stat"><dt>Ambulans terdaftar</dt><dd>{{ $statistik['ambulans'] }}</dd></div>
          <div class="sg-stat"><dt>Jenis layanan</dt><dd>{{ $statistik['layanan'] }}</dd></div>
        </dl>
      </div>
    </section>

    {{-- 3. Cara kerja --}}
    <section class="sg-bagian" id="sg-cara-kerja" aria-labelledby="sg-judul-cara">
      <div class="sg-wadah sg-split">
        <div class="sg-split-judul">
          <h2 id="sg-judul-cara">Cara kerja</h2>
          <p class="sg-lead">Dari mengajukan pesanan sampai ambulans tiba, setiap langkah bisa Anda pantau di aplikasi.</p>
          <p class="sg-petunjuk-js">Ketuk tiap langkah untuk melihat contohnya.</p>
          <div class="sg-stepper" id="sg-stepper" role="group" aria-label="Langkah-langkah"></div>
        </div>

        <div class="sg-kanan">
          <ol class="sg-langkah">
            @foreach ($langkahDemo as $langkah)
              <li data-status="{{ $langkah['status'] }}">
                <h3>{{ $langkah['judul'] }}</h3>
                <p>{{ $langkah['teks'] }}</p>
              </li>
            @endforeach
          </ol>

          <div class="sg-demo" id="sg-demo" hidden>
            <div class="sg-demo-kepala">
              <span class="sg-demo-judul">Contoh tampilan pesanan</span>
              <span class="sg-chip" id="sg-chip" aria-live="polite">{{ $langkahDemo[0]['status'] }}</span>
            </div>
            <p class="sg-demo-teks" id="sg-demo-teks"></p>
            <div class="sg-demo-isi">
              <svg class="sg-demo-peta" id="sg-demo-peta" viewBox="0 0 360 200" aria-hidden="true" focusable="false">
                <rect width="360" height="200" fill="var(--mist)"/>
                <path d="M60 0v200M120 0v200M180 0v200M240 0v200M300 0v200M0 40h360M0 80h360M0 120h360M0 160h360" stroke="var(--line)" stroke-width="1" fill="none"/>
                <path d="M-10 184C60 170 110 198 190 182S310 158 370 170" fill="none" stroke="var(--color-surface-container-lowest)" stroke-width="16"/>
                <path d="M236-10C228 50 262 100 244 210" fill="none" stroke="var(--color-surface-container-lowest)" stroke-width="12"/>
                <path id="sg-m-rute" d="M64 150C130 150 110 92 180 100S252 108 296 52" fill="none" stroke="var(--teal-700)" stroke-width="4" stroke-linecap="round"/>
                <g class="sg-f" id="sg-m-faskes">
                  <rect x="48" y="134" width="32" height="32" rx="8" fill="var(--teal-700)"/>
                  <path d="M64 141v18M55 150h18" stroke="var(--putih)" stroke-width="4" stroke-linecap="round"/>
                  <text x="48" y="192" font-size="13" font-weight="600" fill="var(--laut)" font-family="system-ui,sans-serif">Faskes terdekat</text>
                </g>
                <circle class="sg-halo" cx="296" cy="52" r="22"/>
                <circle cx="296" cy="52" r="10" fill="var(--laut)"/>
                <circle cx="296" cy="52" r="3.5" fill="var(--putih)"/>
                <text x="296" y="22" font-size="13" font-weight="600" fill="var(--laut)" text-anchor="middle" font-family="system-ui,sans-serif">Lokasi jemput</text>
                <g class="sg-kendaraan" id="sg-m-dot" transform="translate(64 150)">
                  <circle r="8" fill="var(--putih)" stroke="var(--teal-700)" stroke-width="4"/>
                </g>
              </svg>
              <ol class="sg-linimasa" id="sg-linimasa" aria-label="Contoh linimasa pesanan">
                @foreach ($langkahDemo as $i => $langkah)
                  <li @if ($i > 0) class="sg-belum" aria-hidden="true" @endif>{{ $langkah['status'] }}</li>
                @endforeach
                <li class="sg-belum" aria-hidden="true">Ambulans tiba</li>
              </ol>
            </div>
            <div class="sg-demo-kaki">
              <button type="button" class="sg-tombol sg-tombol-garis sg-kecil" id="sg-putar">Putar contoh</button>
              <p class="sg-catatan">Contoh alur, bukan pesanan nyata. Nama status mengikuti status pesanan di aplikasi.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- 4. Layanan --}}
    <section class="sg-bagian sg-bagian-pucat" id="sg-layanan" aria-labelledby="sg-judul-layanan">
      <div class="sg-wadah">
        <h2 id="sg-judul-layanan">Layanan yang tersedia</h2>
        <p class="sg-lead">Pilih sesuai keadaan pasien.</p>
        <ul class="sg-layanan">
          @foreach (\App\Enums\JenisLayanan::cases() as $jenis)
            <li @class(['sg-darurat' => $jenis === \App\Enums\JenisLayanan::Darurat])>
              <span class="sg-ikon"><x-ui.ikon :nama="$jenis->ikon()" :ukuran="26" /></span>
              <h3>{{ $jenis->label() }}</h3>
              <p>
                {{ $jenis->deskripsi() }}
                @if ($jenis->wajibFaskesTujuan())
                  Daftarnya ada di <a href="{{ route('faskes.index') }}">Info Faskes</a>.
                @endif
              </p>
              <a class="sg-tombol @if ($jenis === \App\Enums\JenisLayanan::Darurat) sg-tombol-utama @else sg-tombol-garis @endif sg-kecil sg-pilih" href="{{ $aksiUtama['url'] }}">Pesan {{ mb_strtolower($jenis->label()) }}</a>
            </li>
          @endforeach
        </ul>
      </div>
    </section>

    {{-- Info Faskes --}}
    <section class="sg-bagian" id="sg-info-faskes" aria-labelledby="sg-judul-faskes">
      <div class="sg-wadah">
        <div class="sg-band">
          <div>
            <h2 id="sg-judul-faskes">Info Faskes, tanpa akun</h2>
            <p>Lihat rumah sakit, Puskesmas, dan klinik se-Kota Sorong: ketersediaan bed IGD, bed rawat inap, dokter umum dan spesialis, serta ambulans. Data diperbarui oleh petugas masing-masing Faskes.</p>
          </div>
          <a class="sg-tombol sg-tombol-garis" href="{{ route('faskes.index') }}">Buka Info Faskes</a>
        </div>
      </div>
    </section>

    {{-- 5. Mengapa memilih layanan ini --}}
    <section class="sg-bagian sg-bagian-pucat" id="sg-manfaat" aria-labelledby="sg-judul-manfaat">
      <div class="sg-wadah">
        <h2 id="sg-judul-manfaat">Mengapa memilih layanan ini</h2>
        <ul class="sg-manfaat">
          <li>
            <span class="sg-cek" aria-hidden="true"><x-ui.ikon nama="check" :ukuran="18" /></span>
            <div><h3>Diverifikasi resmi</h3><p>Dinas Kesehatan memeriksa setiap pesanan sebelum ambulans ditugaskan.</p></div>
          </li>
          <li>
            <span class="sg-cek" aria-hidden="true"><x-ui.ikon nama="check" :ukuran="18" /></span>
            <div><h3>Dari Faskes terdekat</h3><p>Dinkes yang menentukan ambulans dari Faskes terdekat. Anda tidak perlu mencari sendiri.</p></div>
          </li>
          <li>
            <span class="sg-cek" aria-hidden="true"><x-ui.ikon nama="check" :ukuran="18" /></span>
            <div><h3>Bisa dipantau langsung</h3><p>Lihat status pesanan dan lokasi sopir di aplikasi.</p></div>
          </li>
          <li>
            <span class="sg-cek" aria-hidden="true"><x-ui.ikon nama="check" :ukuran="18" /></span>
            <div><h3>Tercatat transparan</h3><p>Setiap perubahan status masuk ke linimasa pesanan, jadi Anda tahu sudah sampai mana prosesnya.</p></div>
          </li>
        </ul>
      </div>
    </section>

    {{-- 6. Jalur darurat cadangan --}}
    @if ($kontak)
      <section class="sg-bagian sg-gelap sg-cadangan" id="sg-telepon-darurat" aria-labelledby="sg-judul-cadangan">
        <div class="sg-wadah">
          <div class="sg-judul">
            <span class="sg-badge-telp" aria-hidden="true"><x-ui.ikon nama="phone" :ukuran="26" /></span>
            <div>
              <h2 id="sg-judul-cadangan">Aplikasi tidak bisa dibuka?</h2>
              <p>Telepon {{ $kontak['nama'] }}. Pakai jalur ini bila internet atau aplikasi sedang bermasalah.</p>
            </div>
          </div>
          <div class="sg-tel-aksi">
            <a class="sg-tombol sg-tombol-terang sg-tombol-besar" href="tel:{{ $kontak['tel'] }}">
              <x-ui.ikon nama="phone" :ukuran="24" />
              <span>{{ $kontak['nomor'] }}</span>
            </a>
            <button type="button" class="sg-tombol sg-kecil sg-salin" id="sg-salin" data-nomor="{{ $kontak['nomor'] }}">Salin nomor</button>
            <span class="sr-only" id="sg-salin-status" role="status" aria-live="polite"></span>
          </div>
        </div>
      </section>
    @endif

    {{-- 7. Penutup --}}
    <section class="sg-bagian sg-bagian-pucat sg-cta" aria-labelledby="sg-judul-cta">
      <div class="sg-wadah">
        <h2 id="sg-judul-cta">Butuh ambulans?</h2>
        <p class="sg-lead">Ajukan pesanan lewat aplikasi, atau lihat Info Faskes lebih dulu.</p>
        <div class="sg-aksi">
          <a class="sg-tombol sg-tombol-utama" href="{{ $aksiUtama['url'] }}">{{ $aksiUtama['label'] }}</a>
          <a class="sg-tombol sg-tombol-garis" href="{{ route('faskes.index') }}">Info Faskes</a>
        </div>
      </div>
    </section>

  </main>

  {{-- 8. Kaki halaman --}}
  <footer class="sg-kaki">
    <div class="sg-wadah">
      <div class="sg-merek">
        <span class="sg-merek-ikon" aria-hidden="true"><x-ui.ikon nama="plus" :ukuran="22" /></span>
        <span><strong>SIGAP Ambulans</strong><small>Dinas Kesehatan Kota Sorong</small></span>
      </div>
      <p>Sistem informasi layanan ambulans terpadu milik Dinas Kesehatan Kota Sorong.</p>
      <nav aria-label="Tautan kaki halaman">
        <a href="{{ route('faskes.index') }}">Info Faskes</a>
        @auth
          <a href="{{ route(auth()->user()->role->ruteBeranda()) }}">Ke dasbor</a>
        @else
          <a href="{{ route('login') }}">Masuk</a>
        @endauth
      </nav>
      <p class="sg-hak">&copy; {{ now()->year }} Dinas Kesehatan Kota Sorong.</p>
    </div>
  </footer>

  {{-- Bar aksi: muncul di HP setelah tombol di hero tergulir lewat --}}
  <div class="sg-bar" id="sg-bar" role="region" aria-label="Aksi cepat">
    <a class="sg-tombol sg-tombol-utama sg-pesan" href="{{ $aksiUtama['url'] }}">{{ $aksiUtama['label'] }}</a>
    @if ($kontak)
      <a class="sg-tombol sg-tombol-garis sg-telp" href="tel:{{ $kontak['tel'] }}" aria-label="Telepon {{ $kontak['nama'] }}">
        <x-ui.ikon nama="phone" :ukuran="20" />
        Telepon
      </a>
    @endif
  </div>
</div>

<script>
(function () {
  var sg = document.querySelector('.sg');
  if (!sg) return;
  sg.classList.add('sg-js');

  /* Contoh alur pesanan di bagian "Cara kerja". Teks diambil dari <ol class="sg-langkah"> yang dirender server. */
  (function () {
    var kartu = document.getElementById('sg-demo');
    var wadah = document.getElementById('sg-stepper');
    var daftar = document.querySelectorAll('.sg-langkah li');
    if (!kartu || !wadah || !daftar.length) return;

    var kurangGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var chip = document.getElementById('sg-chip');
    var teks = document.getElementById('sg-demo-teks');
    var peta = document.getElementById('sg-demo-peta');
    var rute = document.getElementById('sg-m-rute');
    var faskes = document.getElementById('sg-m-faskes');
    var dot = document.getElementById('sg-m-dot');
    var log = document.querySelectorAll('#sg-linimasa li');
    var putar = document.getElementById('sg-putar');

    var uraian = [], status = [], tombol = [];
    var tiba = false, main = false, raf = 0, timers = [], panjang = 0;

    Array.prototype.forEach.call(daftar, function (li, i) {
      uraian[i] = li.querySelector('p').textContent.trim();
      status[i] = li.getAttribute('data-status') || '';
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'sg-st';
      var no = document.createElement('span');
      no.className = 'sg-no'; no.setAttribute('aria-hidden', 'true'); no.textContent = String(i + 1);
      var nama = document.createElement('span');
      nama.className = 'sg-nama'; nama.textContent = li.querySelector('h3').textContent.trim();
      b.appendChild(no); b.appendChild(nama);
      b.addEventListener('click', function () { pilih(i); });
      wadah.appendChild(b);
      tombol[i] = b;
    });

    kartu.hidden = false;
    panjang = rute.getTotalLength();
    rute.style.strokeDasharray = String(panjang);
    rute.style.strokeDashoffset = String(panjang);

    function setDot(p) {
      var t = rute.getPointAtLength(Math.max(0, Math.min(1, p)) * panjang);
      dot.setAttribute('transform', 'translate(' + t.x + ' ' + t.y + ')');
    }
    function setLog(i, sudahTiba) {
      Array.prototype.forEach.call(log, function (li, j) {
        var tampak = sudahTiba || j <= i;
        var sekarang = sudahTiba ? j === log.length - 1 : j === i;
        li.className = !tampak ? 'sg-belum' : (sekarang ? 'sg-kini' : 'sg-selesai');
        li.setAttribute('aria-hidden', tampak ? 'false' : 'true');
      });
    }
    function setPutar() {
      putar.textContent = main ? 'Hentikan' : (tiba ? 'Putar ulang' : 'Putar contoh');
    }
    function henti() {
      if (raf) { cancelAnimationFrame(raf); raf = 0; }
      timers.forEach(clearTimeout); timers = [];
    }
    function sampai() {
      raf = 0; tiba = true; main = false;
      chip.textContent = 'Ambulans tiba';
      chip.classList.add('sg-tiba');
      peta.classList.add('sg-tiba-peta');
      setLog(daftar.length - 1, true);
      setPutar();
    }
    function jalan() {
      setDot(0);
      if (kurangGerak) { setDot(1); sampai(); return; }
      var mulai = null, durasi = 3600;
      function langkah(t) {
        if (mulai === null) mulai = t;
        var p = Math.min((t - mulai) / durasi, 1);
        setDot(p < 0.5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2);
        if (p < 1) raf = requestAnimationFrame(langkah); else sampai();
      }
      raf = requestAnimationFrame(langkah);
    }
    function tampilkan(i) {
      if (raf) { cancelAnimationFrame(raf); raf = 0; }
      tiba = false;
      tombol.forEach(function (b, j) {
        b.classList.toggle('sg-selesai', j < i);
        if (j === i) b.setAttribute('aria-current', 'step'); else b.removeAttribute('aria-current');
      });
      teks.textContent = uraian[i];
      chip.textContent = status[i];
      chip.classList.remove('sg-tiba');
      peta.classList.remove('sg-tiba-peta');
      setLog(i, false);
      faskes.classList.toggle('sg-tampak', i >= 2);
      rute.style.transition = i === daftar.length - 1 ? 'none' : '';
      rute.style.strokeDashoffset = i >= 2 ? '0' : String(panjang);
      dot.classList.toggle('sg-tampak', i >= daftar.length - 1);
      setDot(0);
      if (i === daftar.length - 1) jalan();
      setPutar();
    }
    function pilih(i) {
      henti(); main = false;
      tampilkan(i);
    }

    putar.addEventListener('click', function () {
      if (main) { henti(); main = false; setPutar(); return; }
      henti(); main = true;
      var n = 0;
      tampilkan(0);
      (function maju() {
        timers.push(setTimeout(function () {
          n++; tampilkan(n);
          if (n < daftar.length - 1) maju();
        }, 1800));
      })();
    });

    tampilkan(0);
    sg.classList.add('sg-demo-aktif');
  })();

  /* Bar aksi di bawah layar HP: muncul setelah tombol hero tergulir lewat, sembunyi di bagian bawah halaman. */
  (function () {
    var bar = document.getElementById('sg-bar');
    var penanda = document.querySelector('.sg-hero .sg-aksi');
    if (!bar || !penanda || !('IntersectionObserver' in window)) return;
    var lewat = false, dalam = [];
    function terapkan() {
      var zona = dalam.filter(Boolean).length;
      bar.classList.toggle('sg-tampil', lewat && zona === 0);
    }
    new IntersectionObserver(function (es) {
      var e = es[es.length - 1];
      lewat = !e.isIntersecting && e.boundingClientRect.top < 0;
      terapkan();
    }).observe(penanda);
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { dalam[e.target._sgZ] = e.isIntersecting; });
      terapkan();
    });
    ['#sg-telepon-darurat', '.sg-cta', '.sg-kaki'].forEach(function (sel, i) {
      var el = document.querySelector(sel);
      if (el) { el._sgZ = i; dalam[i] = false; io.observe(el); }
    });
  })();

  /* Salin nomor telepon (untuk komputer, tempat tautan tel: biasanya tidak jalan). */
  (function () {
    var btn = document.getElementById('sg-salin');
    var status = document.getElementById('sg-salin-status');
    if (!btn) return;
    var asal = btn.textContent, tunggu = 0;
    function berhasil() {
      btn.textContent = 'Nomor tersalin';
      status.textContent = 'Nomor tersalin';
      clearTimeout(tunggu);
      tunggu = setTimeout(function () { btn.textContent = asal; status.textContent = ''; }, 2200);
    }
    function cadangan(nilai) {
      try {
        var ta = document.createElement('textarea');
        ta.value = nilai; ta.setAttribute('readonly', '');
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
        document.body.appendChild(ta); ta.select();
        var ok = document.execCommand('copy');
        document.body.removeChild(ta);
        if (ok) berhasil();
      } catch (e) {}
    }
    btn.addEventListener('click', function () {
      var nilai = btn.getAttribute('data-nomor') || '';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(nilai).then(berhasil, function () { cadangan(nilai); });
      } else { cadangan(nilai); }
    });
  })();
})();
</script>
</x-layouts.publik>
