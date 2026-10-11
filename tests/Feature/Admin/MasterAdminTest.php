<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisAmbulans;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class MasterAdminTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
        $this->admin = $this->buatAdmin();
    }

    // Ambulans

    private function isianAmbulans(Faskes $faskes, array $ganti = []): array
    {
        return array_merge([
            'plat_nomor' => 'PB-9999-ZZ',
            'faskes_id' => $faskes->id,
            'jenis' => 'darurat',
            'merek_model' => 'Toyota Hiace',
            'status' => 'tersedia',
        ], $ganti);
    }

    public function test_daftar_dan_filter_ambulans(): void
    {
        $a = Faskes::factory()->create(['nama' => 'Faskes A']);
        $b = Faskes::factory()->create(['nama' => 'Faskes B']);
        Ambulans::factory()->darurat()->create(['faskes_id' => $a->id, 'plat_nomor' => 'PB-A-1']);
        Ambulans::factory()->servis()->create(['faskes_id' => $b->id, 'plat_nomor' => 'PB-B-1']);

        $this->actingAs($this->admin)->get(route('admin.ambulans.index'))
            ->assertOk()->assertSee('PB-A-1')->assertSee('PB-B-1')->assertSee('Servis');
        $this->get(route('admin.ambulans.index', ['faskes_id' => $a->id]))->assertSee('PB-A-1')->assertDontSee('PB-B-1');
        $this->get(route('admin.ambulans.index', ['status' => 'servis']))->assertSee('PB-B-1')->assertDontSee('PB-A-1');
        $this->get(route('admin.ambulans.index', ['jenis' => 'darurat']))->assertSee('PB-A-1')->assertDontSee('PB-B-1');
    }

    public function test_tambah_ambulans_dengan_sopir_tetap(): void
    {
        $faskes = Faskes::factory()->create();
        $sopir = $this->buatSopir($faskes);

        $this->actingAs($this->admin)->get(route('admin.ambulans.create'))->assertOk()->assertSee('Plat nomor');
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes, ['sopir_id' => $sopir->id]))
            ->assertRedirect(route('admin.ambulans.index'))->assertSessionHas('sukses');

        $a = Ambulans::where('plat_nomor', 'PB-9999-ZZ')->firstOrFail();
        $this->assertSame(JenisAmbulans::Darurat, $a->jenis);
        $this->assertSame(StatusAmbulans::Tersedia, $a->status);
        $this->assertSame($sopir->id, $a->sopir_id);
    }

    public function test_validasi_ambulans(): void
    {
        $faskes = Faskes::factory()->create();
        $nonaktif = Faskes::factory()->nonaktif()->create();
        $sopirLain = $this->buatSopir();
        Ambulans::factory()->create(['plat_nomor' => 'PB-DUP-1']);

        $this->actingAs($this->admin);
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes, ['plat_nomor' => 'PB-DUP-1']))->assertSessionHasErrors('plat_nomor');
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($nonaktif))->assertSessionHasErrors('faskes_id');
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes, ['sopir_id' => $sopirLain->id]))->assertSessionHasErrors('sopir_id');
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes, ['sopir_id' => $this->buatWarga()->id]))->assertSessionHasErrors('sopir_id');
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes, ['jenis' => 'ngawur']))->assertSessionHasErrors('jenis');
        $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes, ['status' => 'bertugas']))->assertSessionHasErrors('status');
        $this->assertSame(1, Ambulans::count());
    }

    public function test_ubah_ambulans_tersedia_ke_servis_dan_plat_boleh_sama(): void
    {
        $faskes = Faskes::factory()->create();
        $a = Ambulans::factory()->create(['faskes_id' => $faskes->id, 'plat_nomor' => 'PB-UBAH-1']);

        $this->actingAs($this->admin)->get(route('admin.ambulans.edit', $a))->assertOk()->assertSee('value="PB-UBAH-1"', false);
        $this->put(route('admin.ambulans.update', $a), $this->isianAmbulans($faskes, ['plat_nomor' => 'PB-UBAH-1', 'status' => 'servis']))
            ->assertRedirect(route('admin.ambulans.index'));

        $this->assertSame(StatusAmbulans::Servis, $a->fresh()->status);
    }

    public function test_status_bertugas_tidak_bisa_diubah_manual(): void
    {
        $faskes = Faskes::factory()->create();
        $a = Ambulans::factory()->bertugas()->create(['faskes_id' => $faskes->id, 'plat_nomor' => 'PB-TUGAS-1']);

        $this->actingAs($this->admin)->get(route('admin.ambulans.edit', $a))
            ->assertOk()->assertSee('Status berubah otomatis')->assertDontSee('name="status"', false);

        $this->put(route('admin.ambulans.update', $a), $this->isianAmbulans($faskes, ['plat_nomor' => 'PB-TUGAS-1', 'status' => 'tersedia', 'merek_model' => 'Baru']))
            ->assertRedirect(route('admin.ambulans.index'));

        $a->refresh();
        $this->assertSame(StatusAmbulans::Bertugas, $a->status);
        $this->assertSame('Baru', $a->merek_model);
    }

    public function test_ambulans_tidak_punya_rute_hapus(): void
    {
        $a = Ambulans::factory()->create();

        $this->actingAs($this->admin)->delete('/admin/ambulans/'.$a->id)->assertStatus(405);
        $this->assertSame(1, Ambulans::count());
    }

    // Sopir dan Petugas Faskes

    public static function jenisAkun(): array
    {
        return [
            'sopir' => ['sopir', Role::Sopir],
            'petugas' => ['petugas', Role::PetugasFaskes],
        ];
    }

    private function buatAkun(Role $peran, ?Faskes $faskes = null, array $atribut = []): User
    {
        return $peran === Role::Sopir ? $this->buatSopir($faskes, $atribut) : $this->buatPetugas($faskes, $atribut);
    }

    private function isianAkun(Faskes $faskes, array $ganti = []): array
    {
        return array_merge([
            'name' => 'Akun Baru',
            'email' => 'akun.baru@sigap.test',
            'no_telepon' => '081234567890',
            'faskes_id' => $faskes->id,
            'password' => 'rahasia123',
            'aktif' => '1',
        ], $ganti);
    }

    #[DataProvider('jenisAkun')]
    public function test_daftar_akun_hanya_peran_yang_sesuai_dengan_filter(string $rute, Role $peran): void
    {
        $a = Faskes::factory()->create(['nama' => 'Faskes A']);
        $b = Faskes::factory()->create(['nama' => 'Faskes B']);
        $this->buatAkun($peran, $a, ['name' => 'Akun Satu']);
        $this->buatAkun($peran, $b, ['name' => 'Akun Dua']);
        $lain = $peran === Role::Sopir ? $this->buatPetugas($a, ['name' => 'Peran Lain']) : $this->buatSopir($a, ['name' => 'Peran Lain']);

        $this->actingAs($this->admin)->get(route("admin.$rute.index"))
            ->assertOk()->assertSee('Akun Satu')->assertSee('Akun Dua')->assertDontSee($lain->name);
        $this->get(route("admin.$rute.index", ['faskes_id' => $a->id]))->assertSee('Akun Satu')->assertDontSee('Akun Dua');
        $this->get(route("admin.$rute.index", ['cari' => 'Dua']))->assertSee('Akun Dua')->assertDontSee('Akun Satu');
    }

    #[DataProvider('jenisAkun')]
    public function test_tambah_akun_menetapkan_peran_dari_controller_dan_menyimpan_sandi_ter_hash(string $rute, Role $peran): void
    {
        $faskes = Faskes::factory()->create();

        $this->actingAs($this->admin)->get(route("admin.$rute.create"))->assertOk();
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['role' => 'admin']))
            ->assertRedirect(route("admin.$rute.index"))->assertSessionHas('sukses');

        $akun = User::where('email', 'akun.baru@sigap.test')->firstOrFail();
        $this->assertSame($peran, $akun->role);
        $this->assertSame($faskes->id, $akun->faskes_id);
        $this->assertTrue(Hash::check('rahasia123', $akun->password));
    }

    #[DataProvider('jenisAkun')]
    public function test_validasi_akun(string $rute, Role $peran): void
    {
        $faskes = Faskes::factory()->create();
        $ada = $this->buatAkun($peran, $faskes, ['email' => 'ada@sigap.test']);

        $this->actingAs($this->admin);
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['email' => 'ada@sigap.test']))->assertSessionHasErrors('email');
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['faskes_id' => null]))->assertSessionHasErrors('faskes_id');
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['faskes_id' => 99999]))->assertSessionHasErrors('faskes_id');
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['password' => '']))->assertSessionHasErrors('password');
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['password' => 'pendek']))->assertSessionHasErrors('password');
        $this->post(route("admin.$rute.store"), $this->isianAkun($faskes, ['no_telepon' => 'abc']))->assertSessionHasErrors('no_telepon');
        $this->assertSame(1, User::where('role', $peran->value)->count());
        $this->assertNotNull($ada);
    }

    #[DataProvider('jenisAkun')]
    public function test_ubah_akun_sandi_kosong_tidak_mengubah_sandi(string $rute, Role $peran): void
    {
        $faskes = Faskes::factory()->create();
        $akun = $this->buatAkun($peran, $faskes, ['email' => 'lama@sigap.test']);
        $sandiLama = $akun->password;

        $this->actingAs($this->admin)->get(route("admin.$rute.edit", $akun))->assertOk()->assertSee('Kosongkan bila tidak diubah.');
        $this->put(route("admin.$rute.update", $akun), $this->isianAkun($faskes, ['email' => 'lama@sigap.test', 'name' => 'Nama Baru', 'password' => '']))
            ->assertRedirect(route("admin.$rute.index"));

        $akun->refresh();
        $this->assertSame('Nama Baru', $akun->name);
        $this->assertSame($sandiLama, $akun->password);
        $this->assertSame($peran, $akun->role);

        $this->put(route("admin.$rute.update", $akun), $this->isianAkun($faskes, ['email' => 'lama@sigap.test', 'password' => 'sandibaru99']));
        $this->assertTrue(Hash::check('sandibaru99', $akun->fresh()->password));
    }

    #[DataProvider('jenisAkun')]
    public function test_aktif_membalik_status_dan_mematikan_online(string $rute, Role $peran): void
    {
        $akun = $this->buatAkun($peran, null, ['sedang_online' => true]);

        $this->actingAs($this->admin)->put(route("admin.$rute.aktif", $akun))->assertRedirect(route("admin.$rute.index"))->assertSessionHas('sukses');
        $akun->refresh();
        $this->assertFalse($akun->aktif);
        $this->assertFalse($akun->sedang_online);

        $this->put(route("admin.$rute.aktif", $akun));
        $this->assertTrue($akun->fresh()->aktif);
    }

    #[DataProvider('jenisAkun')]
    public function test_binding_peran_akun_peran_lain_404_dan_tanpa_hapus(string $rute, Role $peran): void
    {
        $lain = $peran === Role::Sopir ? $this->buatPetugas() : $this->buatSopir();
        $akun = $this->buatAkun($peran);

        $this->actingAs($this->admin);
        $this->get(route("admin.$rute.edit", $lain))->assertNotFound();
        $this->put(route("admin.$rute.aktif", $lain))->assertNotFound();
        $this->put(route("admin.$rute.update", $lain), [])->assertNotFound();
        $this->delete("/admin/$rute/".$akun->id)->assertStatus(405);
        $this->assertTrue($lain->fresh()->aktif);
    }

    public function test_sopir_dengan_pesanan_aktif_tidak_bisa_dinonaktifkan(): void
    {
        $sopir = $this->buatSopir();
        Pesanan::factory()->berstatus(PesananStatus::Diterima)->create(['sopir_id' => $sopir->id]);

        $this->actingAs($this->admin)->put(route('admin.sopir.aktif', $sopir))->assertSessionHas('galat');
        $this->assertTrue($sopir->fresh()->aktif);
    }

    public function test_dinkes_tidak_bisa_menonaktifkan_diri_sendiri_lewat_pengelolaan_akun(): void
    {
        // Admin tidak termasuk peran yang dikelola: akunnya 404 pada route sopir/petugas.
        $this->actingAs($this->admin)->put(route('admin.sopir.aktif', $this->admin))->assertNotFound();
        $this->put(route('admin.petugas.aktif', $this->admin))->assertNotFound();
        $this->assertTrue($this->admin->fresh()->aktif);
    }

    public function test_master_hanya_untuk_dinkes(): void
    {
        $faskes = Faskes::factory()->create();

        foreach ([$this->buatWarga(), $this->buatPetugas($faskes), $this->buatSopir($faskes)] as $user) {
            $this->actingAs($user);
            foreach (['ambulans', 'sopir', 'petugas'] as $sumber) {
                $this->get(route("admin.$sumber.index"))->assertForbidden();
                $this->get(route("admin.$sumber.create"))->assertForbidden();
            }
            $this->post(route('admin.sopir.store'), $this->isianAkun($faskes))->assertForbidden();
            $this->post(route('admin.ambulans.store'), $this->isianAmbulans($faskes))->assertForbidden();
        }
        $this->assertSame(0, Ambulans::count());
    }
}
