<?php

namespace Tests\Feature\Auth;

use App\Enums\PesananStatus;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\MembuatPengguna;
use Tests\TestCase;

class PeranDanPolicyTest extends TestCase
{
    use MembuatPengguna, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_uji/admin', fn () => 'ok')->middleware(['web', 'auth', 'role:admin']);
        Route::get('/_uji/campur', fn () => 'ok')->middleware(['web', 'auth', 'role:petugas_faskes,sopir']);
        Route::get('/_uji/sopir/{sopir}', fn ($sopir) => $sopir->name)->middleware('web');
        Route::get('/_uji/petugas/{petugas}', fn ($petugas) => $petugas->name)->middleware('web');
        Route::get('/login', fn () => 'login')->name('login');
        Route::getRoutes()->refreshNameLookups();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/_uji/admin')->assertRedirect('/login');
    }

    public function test_peran_yang_tepat_lolos_dan_lainnya_403(): void
    {
        $this->actingAs($this->buatAdmin())->get('/_uji/admin')->assertOk();
        $this->actingAs($this->buatWarga())->get('/_uji/admin')->assertForbidden();
        $this->actingAs($this->buatSopir())->get('/_uji/admin')->assertForbidden();
    }

    public function test_middleware_menerima_beberapa_peran(): void
    {
        $this->actingAs($this->buatSopir())->get('/_uji/campur')->assertOk();
        $this->actingAs($this->buatPetugas())->get('/_uji/campur')->assertOk();
        $this->actingAs($this->buatAdmin())->get('/_uji/campur')->assertForbidden();
    }

    public function test_binding_sopir_dan_petugas_memfilter_peran(): void
    {
        $sopir = $this->buatSopir();
        $petugas = $this->buatPetugas();
        $warga = $this->buatWarga();

        $this->get('/_uji/sopir/'.$sopir->id)->assertOk();
        $this->get('/_uji/sopir/'.$petugas->id)->assertNotFound();
        $this->get('/_uji/sopir/'.$warga->id)->assertNotFound();
        $this->get('/_uji/petugas/'.$petugas->id)->assertOk();
        $this->get('/_uji/petugas/'.$sopir->id)->assertNotFound();
    }

    public function test_policy_pesanan_per_peran(): void
    {
        $faskes = Faskes::factory()->create();
        $lain = Faskes::factory()->create();
        $warga = $this->buatWarga();
        $sopir = $this->buatSopir($faskes);
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id]);
        $pesanan = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create([
            'user_id' => $warga->id, 'sopir_id' => $sopir->id, 'ambulans_id' => $ambulans->id,
        ]);

        $this->assertTrue(Gate::forUser($this->buatAdmin())->allows('view', $pesanan));
        $this->assertTrue(Gate::forUser($warga)->allows('view', $pesanan));
        $this->assertFalse(Gate::forUser($this->buatWarga())->allows('view', $pesanan));
        $this->assertTrue(Gate::forUser($sopir)->allows('view', $pesanan));
        $this->assertFalse(Gate::forUser($this->buatSopir($faskes))->allows('view', $pesanan));
        $this->assertTrue(Gate::forUser($this->buatPetugas($faskes))->allows('view', $pesanan));
        $this->assertFalse(Gate::forUser($this->buatPetugas($lain))->allows('view', $pesanan));
    }

    public function test_policy_batalkan_hanya_pemilik_sebelum_penugasan(): void
    {
        $warga = $this->buatWarga();
        $menunggu = Pesanan::factory()->create(['user_id' => $warga->id]);
        $ditugaskan = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['user_id' => $warga->id]);

        $this->assertTrue(Gate::forUser($warga)->allows('batalkan', $menunggu));
        $this->assertFalse(Gate::forUser($warga)->allows('batalkan', $ditugaskan));
        $this->assertFalse(Gate::forUser($this->buatWarga())->allows('batalkan', $menunggu));
        $this->assertFalse(Gate::forUser($this->buatAdmin())->allows('batalkan', $menunggu));
    }
}
