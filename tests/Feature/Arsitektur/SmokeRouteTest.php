<?php

namespace Tests\Feature\Arsitektur;

use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RuteLaravel;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Tes asap: setiap route GET bernama dapat dibuka peran yang berhak dengan data seed lengkap. */
class SmokeRouteTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, int> */
    private array $param = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->detectEnvironment(fn () => 'local');
        $this->seed(DatabaseSeeder::class);

        $petugas = User::where('email', 'petugas.selebesolu@sigap.test')->firstOrFail();
        $pesananFaskes = Pesanan::whereHas('ambulans', fn ($q) => $q->where('faskes_id', $petugas->faskes_id))->firstOrFail();

        $this->param = [
            'pesanan' => Pesanan::firstOrFail()->id,
            'faskes' => Faskes::firstOrFail()->id,
            'ambulans' => Ambulans::firstOrFail()->id,
            'sopir' => User::sopir()->firstOrFail()->id,
            'petugas' => $petugas->id,
        ];
        $this->param['pesanan_petugas'] = $pesananFaskes->id;
    }

    private function url(RuteLaravel $rute): string
    {
        $uri = $rute->uri();
        foreach ($this->param as $kunci => $id) {
            $uri = str_replace('{'.$kunci.'}', (string) $id, $uri);
        }
        if ($rute->getName() === 'petugas.pesanan.show') {
            $uri = str_replace((string) $this->param['pesanan'], (string) $this->param['pesanan_petugas'], $uri);
        }

        return '/'.ltrim($uri, '/');
    }

    /** @return array<int, RuteLaravel> */
    private function ruteGet(string $awalan): array
    {
        $hasil = [];
        foreach (Route::getRoutes() as $rute) {
            $nama = $rute->getName();
            if ($nama === null || ! in_array('GET', $rute->methods(), true) || str_starts_with($nama, 'storage.')) {
                continue;
            }
            if ($awalan === '' ? ! str_contains($nama, '.') || str_starts_with($nama, 'faskes.') : str_starts_with($nama, $awalan.'.')) {
                $hasil[] = $rute;
            }
        }

        return $hasil;
    }

    private function bukaSemua(string $email, string $awalan, int $minimal): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
        $rute = $this->ruteGet($awalan);
        $this->assertGreaterThanOrEqual($minimal, count($rute), 'Jumlah route '.$awalan);

        foreach ($rute as $r) {
            $respons = $this->get($this->url($r));
            if ($r->getName() === 'warga.pesanan.konfirmasi') {
                // tanpa draf di session, halaman konfirmasi dialihkan ke form
                $respons->assertRedirect(route('warga.pesanan.buat'));

                continue;
            }
            $this->assertSame(200, $respons->getStatusCode(), $r->getName().' ('.$this->url($r).') sebagai '.$email);
        }
    }

    public function test_dinkes_membuka_semua_halaman_admin(): void
    {
        $this->bukaSemua('admin@sigap.test', 'admin', 14);
    }

    public function test_masyarakat_membuka_semua_halaman_warga(): void
    {
        $this->bukaSemua('warga@sigap.test', 'warga', 5);
    }

    public function test_petugas_membuka_semua_halaman_petugas(): void
    {
        $this->bukaSemua('petugas.selebesolu@sigap.test', 'petugas', 3);
    }

    public function test_sopir_membuka_semua_halaman_sopir(): void
    {
        $this->bukaSemua('sopir.selebesolu@sigap.test', 'sopir', 2);
    }

    public function test_tamu_membuka_halaman_publik_dan_auth(): void
    {
        auth()->logout();
        foreach (['beranda', 'faskes.index', 'faskes.data', 'login', 'register', 'ui.katalog'] as $nama) {
            $this->get(route($nama))->assertOk();
        }
    }

    public function test_halaman_pesanan_demo_menampilkan_semua_status_di_daftar_dinkes(): void
    {
        $this->actingAs(User::where('email', 'admin@sigap.test')->firstOrFail());

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Pesanan terbaru');
        $this->get(route('admin.pesanan.index'))->assertOk()
            ->assertSee('Menunggu verifikasi')->assertSee('Dalam perjalanan')->assertSee('Selesai')->assertSee('Ditolak')->assertSee('Dibatalkan');
        $this->get(route('admin.laporan.pdf'))->assertOk();
        $this->get(route('admin.laporan.csv'))->assertOk();
    }
}
