<?php

namespace Tests\Feature\Arsitektur;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RuteLaravel;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Matriks otorisasi: setiap route bernama x setiap peran (tamu, Masyarakat, Dinkes, Petugas Faskes, Sopir). */
class OtorisasiMatriksTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, int> */
    private array $param = [];

    /** @var array<string, User> */
    private array $pengguna = [];

    private int $pesananMenunggu;

    protected function setUp(): void
    {
        parent::setUp();

        $faskes = Faskes::factory()->create();
        $warga = User::factory()->warga()->create();
        $sopir = User::factory()->sopir($faskes)->online()->create();
        $petugas = User::factory()->petugas($faskes)->create();
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id, 'sopir_id' => $sopir->id]);
        $pesanan = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create([
            'user_id' => $warga->id, 'sopir_id' => $sopir->id, 'ambulans_id' => $ambulans->id,
        ]);

        $this->pesananMenunggu = Pesanan::factory()->create(['user_id' => $warga->id])->id;
        $this->pengguna = [
            'warga' => $warga, 'admin' => User::factory()->admin()->create(), 'petugas_faskes' => $petugas, 'sopir' => $sopir,
        ];
        $this->param = [
            'pesanan' => $pesanan->id, 'faskes' => $faskes->id, 'ambulans' => $ambulans->id,
            'sopir' => $sopir->id, 'petugas' => $petugas->id,
        ];
    }

    /** @return array<int, RuteLaravel> */
    private function ruteAplikasi(): array
    {
        $hasil = [];
        foreach (Route::getRoutes() as $rute) {
            $nama = $rute->getName();
            if ($nama === null || str_starts_with($nama, 'storage.') || $nama === 'ui.katalog' || $nama === 'logout') {
                continue;
            }
            $hasil[] = $rute;
        }

        return $hasil;
    }

    private function url(RuteLaravel $rute): string
    {
        $uri = $rute->uri();
        $param = $this->param;
        // Pembatalan oleh Masyarakat hanya sah pada pesanan yang belum ditugaskan.
        if ($rute->getName() === 'warga.pesanan.batalkan') {
            $param['pesanan'] = $this->pesananMenunggu;
        }
        foreach ($param as $kunci => $id) {
            $uri = str_replace('{'.$kunci.'}', (string) $id, $uri);
        }

        return '/'.ltrim($uri, '/');
    }

    private function metode(RuteLaravel $rute): string
    {
        return collect($rute->methods())->first(fn ($m) => ! in_array($m, ['HEAD', 'PATCH'], true));
    }

    /** Peran yang berhak, atau 'publik' / 'tamu'. */
    private function pemilik(string $nama): string
    {
        foreach (['warga' => 'warga', 'admin' => 'admin', 'petugas' => 'petugas_faskes', 'sopir' => 'sopir'] as $awalan => $peran) {
            if (str_starts_with($nama, $awalan.'.')) {
                return $peran;
            }
        }

        return in_array($nama, ['login', 'login.proses', 'register', 'register.proses'], true) ? 'tamu' : 'publik';
    }

    private function panggil(RuteLaravel $rute, ?User $pengguna): \Illuminate\Testing\TestResponse
    {
        if ($pengguna) {
            $this->actingAs($pengguna);
        } else {
            auth()->logout();
        }

        return $this->call($this->metode($rute), $this->url($rute));
    }

    public function test_tamu_diarahkan_ke_login_pada_semua_route_terlindungi(): void
    {
        $jumlah = 0;
        foreach ($this->ruteAplikasi() as $rute) {
            if (! in_array($this->pemilik($rute->getName()), ['warga', 'admin', 'petugas_faskes', 'sopir'], true)) {
                continue;
            }
            $respons = $this->panggil($rute, null);
            $this->assertSame(302, $respons->getStatusCode(), $rute->getName().' untuk tamu');
            $this->assertStringEndsWith('/login', $respons->headers->get('Location'), $rute->getName().' untuk tamu');
            $jumlah++;
        }
        $this->assertGreaterThan(40, $jumlah, 'Matriks harus mencakup seluruh route peran.');
    }

    public function test_peran_lain_mendapat_403_dan_peran_yang_berhak_lolos(): void
    {
        $diperiksa = 0;
        foreach ($this->ruteAplikasi() as $rute) {
            $pemilik = $this->pemilik($rute->getName());
            if (! in_array($pemilik, ['warga', 'admin', 'petugas_faskes', 'sopir'], true)) {
                continue;
            }

            foreach ($this->pengguna as $peran => $user) {
                $kode = $this->panggil($rute, $user)->getStatusCode();
                $label = $rute->getName().' sebagai '.$peran;
                if ($peran === $pemilik) {
                    $this->assertNotContains($kode, [401, 403, 404, 405, 500], $label.' (kode '.$kode.')');
                } else {
                    $this->assertSame(403, $kode, $label);
                }
                $diperiksa++;
            }
        }
        $this->assertGreaterThan(160, $diperiksa);
    }

    public function test_route_publik_terbuka_untuk_semua(): void
    {
        foreach ($this->ruteAplikasi() as $rute) {
            if ($this->pemilik($rute->getName()) !== 'publik') {
                continue;
            }
            foreach ([null, ...array_values($this->pengguna)] as $user) {
                $this->assertSame(200, $this->panggil($rute, $user)->getStatusCode(), $rute->getName().' sebagai '.($user?->role->value ?? 'tamu'));
            }
        }
    }

    public function test_route_tamu_hanya_untuk_yang_belum_masuk(): void
    {
        foreach ($this->ruteAplikasi() as $rute) {
            if ($this->pemilik($rute->getName()) !== 'tamu') {
                continue;
            }
            if (in_array('GET', $rute->methods(), true)) {
                $this->assertSame(200, $this->panggil($rute, null)->getStatusCode(), $rute->getName().' sebagai tamu');
            }
            foreach ($this->pengguna as $peran => $user) {
                $respons = $this->panggil($rute, $user);
                $this->assertSame(302, $respons->getStatusCode(), $rute->getName().' sebagai '.$peran);
                $this->assertStringEndsWith(route($user->role->ruteBeranda(), absolute: false), $respons->headers->get('Location'));
            }
        }
    }

    public function test_logout_hanya_untuk_pengguna_masuk(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
        foreach ($this->pengguna as $user) {
            $this->actingAs($user)->post(route('logout'))->assertRedirect(route('beranda'));
        }
    }

    public function test_setiap_peran_terdaftar_punya_ruteBeranda_yang_ada(): void
    {
        foreach (Role::cases() as $peran) {
            $this->assertTrue(Route::has($peran->ruteBeranda()), $peran->value);
        }
    }
}
