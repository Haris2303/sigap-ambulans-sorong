<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class AutentikasiTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
        RateLimiter::clear('x');
    }

    public function test_halaman_login_dan_register_tampil_untuk_tamu(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Masuk')->assertSee('Kata sandi');
        $this->get(route('register'))->assertOk()->assertSee('Nomor telepon');
    }

    public static function peran(): array
    {
        return [
            [Role::Warga, 'warga.beranda'],
            [Role::Admin, 'admin.dashboard'],
            [Role::PetugasFaskes, 'petugas.dashboard'],
            [Role::Sopir, 'sopir.dashboard'],
        ];
    }

    #[DataProvider('peran')]
    public function test_login_mengalihkan_sesuai_peran(Role $peran, string $rute): void
    {
        $user = User::factory()->create(['role' => $peran, 'email' => 'uji@sigap.test', 'faskes_id' => null]);

        $this->post(route('login.proses'), ['email' => 'uji@sigap.test', 'password' => 'password'])
            ->assertRedirect(route($rute));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_gagal_dengan_sandi_salah(): void
    {
        $this->buatWarga(['email' => 'uji@sigap.test']);

        $this->from(route('login'))
            ->post(route('login.proses'), ['email' => 'uji@sigap.test', 'password' => 'salah'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    public function test_akun_nonaktif_ditolak_dengan_pesan_sendiri(): void
    {
        $this->buatWarga(['email' => 'uji@sigap.test', 'aktif' => false]);

        $this->post(route('login.proses'), ['email' => 'uji@sigap.test', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => trans('auth.nonaktif')]);

        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_lima_percobaan_gagal(): void
    {
        $this->buatWarga(['email' => 'uji@sigap.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.proses'), ['email' => 'uji@sigap.test', 'password' => 'salah']);
        }

        $respons = $this->post(route('login.proses'), ['email' => 'uji@sigap.test', 'password' => 'password']);

        $respons->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_validasi_login_menolak_isian_kosong(): void
    {
        $this->post(route('login.proses'), [])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_register_membuat_akun_masyarakat_dan_langsung_masuk(): void
    {
        $this->post(route('register.proses'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@contoh.test',
            'no_telepon' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('warga.beranda'));

        $user = User::where('email', 'budi@contoh.test')->firstOrFail();
        $this->assertSame(Role::Warga, $user->role);
        $this->assertTrue($user->aktif);
        $this->assertNull($user->faskes_id);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_tidak_bisa_menyisipkan_peran_lain(): void
    {
        $this->post(route('register.proses'), [
            'name' => 'Penyusup',
            'email' => 'penyusup@contoh.test',
            'no_telepon' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
            'aktif' => '0',
            'faskes_id' => 1,
        ]);

        $user = User::where('email', 'penyusup@contoh.test')->firstOrFail();
        $this->assertSame(Role::Warga, $user->role);
        $this->assertTrue($user->aktif);
        $this->assertNull($user->faskes_id);
    }

    public function test_validasi_register(): void
    {
        $this->buatWarga(['email' => 'ada@contoh.test']);

        $this->post(route('register.proses'), [
            'name' => '', 'email' => 'ada@contoh.test', 'no_telepon' => 'abc',
            'password' => 'pendek', 'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['name', 'email', 'no_telepon', 'password']);

        $this->assertSame(1, User::count());
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs($this->buatWarga())
            ->post(route('logout'))
            ->assertRedirect(route('beranda'));

        $this->assertGuest();
    }

    public function test_logout_butuh_login(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_pengguna_masuk_dialihkan_dari_halaman_tamu(): void
    {
        $this->actingAs($this->buatAdmin())->get(route('login'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->buatWarga())->get(route('register'))->assertRedirect(route('warga.beranda'));
    }

    public function test_akun_yang_dinonaktifkan_saat_masuk_dikeluarkan_oleh_middleware_peran(): void
    {
        Route::get('/_uji/admin', fn () => 'ok')->middleware(['web', 'auth', 'role:admin']);

        $admin = $this->buatAdmin(['aktif' => false]);

        $this->actingAs($admin)->get('/_uji/admin')
            ->assertRedirect(route('login'))
            ->assertSessionHas('galat', trans('auth.nonaktif'));

        $this->assertGuest();
    }
}
