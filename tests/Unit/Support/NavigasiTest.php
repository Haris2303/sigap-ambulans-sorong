<?php

namespace Tests\Unit\Support;

use App\Support\Navigasi;
use PHPUnit\Framework\TestCase;

class NavigasiTest extends TestCase
{
    /** @var array<string, string> */
    private array $templat = [
        'aplikasi' => 'geo:{lat},{lng}?q={lat},{lng}(Lokasi%20jemput)',
        'aplikasi_alamat' => 'geo:0,0?q={alamat}',
        'web' => 'https://peta.test/?mlat={lat}&mlon={lng}#map=17/{lat}/{lng}',
        'web_alamat' => 'https://peta.test/search?query={alamat}',
    ];

    public function test_dengan_koordinat_mengisi_lat_dan_lng_di_kedua_tautan(): void
    {
        $tautan = Navigasi::tautan(-0.8762, 131.2558, 'Jalan Ahmad Yani', $this->templat);

        $this->assertSame('geo:-0.8762,131.2558?q=-0.8762,131.2558(Lokasi%20jemput)', $tautan['aplikasi']);
        $this->assertSame('https://peta.test/?mlat=-0.8762&mlon=131.2558#map=17/-0.8762/131.2558', $tautan['web']);
    }

    public function test_koordinat_berupa_teks_desimal_dibulatkan_tujuh_desimal_tanpa_nol_di_belakang(): void
    {
        $this->assertSame(
            'geo:-0.88,131.25?q=-0.88,131.25(Lokasi%20jemput)',
            Navigasi::tautan('-0.8800000', '131.2500000', 'x', $this->templat)['aplikasi'],
        );
        $this->assertSame(
            'geo:-0.1234568,131.0000001?q=-0.1234568,131.0000001(Lokasi%20jemput)',
            Navigasi::tautan('-0.12345678', '131.00000009', 'x', $this->templat)['aplikasi'],
        );
        $this->assertSame('geo:0,1?q=0,1(Lokasi%20jemput)', Navigasi::tautan(0, 1, 'x', $this->templat)['aplikasi']);
    }

    public function test_tanpa_koordinat_memakai_alamat_yang_di_url_encode(): void
    {
        $tautan = Navigasi::tautan(null, null, ' Jalan Basuki Rahmat Km 8 & sekitarnya, Sorong ', $this->templat);

        $this->assertSame('geo:0,0?q=Jalan%20Basuki%20Rahmat%20Km%208%20%26%20sekitarnya%2C%20Sorong', $tautan['aplikasi']);
        $this->assertSame('https://peta.test/search?query=Jalan%20Basuki%20Rahmat%20Km%208%20%26%20sekitarnya%2C%20Sorong', $tautan['web']);
    }

    public function test_koordinat_setengah_terisi_atau_bukan_angka_dianggap_tidak_ada(): void
    {
        foreach ([[-0.88, null], [null, 131.25], ['abc', '131.25'], ['', '']] as [$lat, $lng]) {
            $this->assertStringStartsWith('geo:0,0?q=', Navigasi::tautan($lat, $lng, 'Jalan Satu', $this->templat)['aplikasi']);
        }
    }

    public function test_alamat_berhuruf_non_latin_dan_tanda_kutip_tidak_merusak_tautan(): void
    {
        $tautan = Navigasi::tautan(null, null, "Jl. O'Brien \"Blok A\" #3", $this->templat);

        $this->assertSame('geo:0,0?q=Jl.%20O%27Brien%20%22Blok%20A%22%20%233', $tautan['aplikasi']);
        $this->assertStringNotContainsString(' ', $tautan['web']);
        $this->assertStringNotContainsString('"', $tautan['web']);
    }
}
