<?php

namespace App\Services;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Exceptions\PenugasanTidakValidException;
use App\Exceptions\TransisiTidakValidException;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu perubahan status pesanan, sopir_id, dan ambulans_id (RANCANGAN bagian 5).
 */
class PesananWorkflow
{
    /** @param array<string, mixed> $data */
    public function buat(User $warga, array $data): Pesanan
    {
        $this->wajibPeran($warga, Role::Warga);

        return DB::transaction(function () use ($warga, $data) {
            $pesanan = new Pesanan($data);
            $pesanan->user_id = $warga->id;
            $pesanan->status = PesananStatus::Menunggu;

            if ($pesanan->jenis_layanan->wajibFaskesTujuan() && empty($pesanan->faskes_tujuan_id)) {
                throw new TransisiTidakValidException('Faskes tujuan wajib diisi untuk pesanan rujukan.');
            }

            $pesanan->save();
            $pesanan->kode = 'SGP-'.str_pad((string) $pesanan->id, 6, '0', STR_PAD_LEFT);
            $pesanan->save();

            $this->catat($pesanan, null, PesananStatus::Menunggu, $warga);

            return $pesanan;
        });
    }

    public function verifikasi(Pesanan $pesanan, User $admin): Pesanan
    {
        $this->wajibPeran($admin, Role::Admin);

        return DB::transaction(function () use ($pesanan, $admin) {
            $pesanan = $this->kunci($pesanan);
            $this->wajibTransisi($pesanan, PesananStatus::Terverifikasi);

            $pesanan->diverifikasi_oleh_id = $admin->id;
            $pesanan->diverifikasi_pada = now();
            $this->pindah($pesanan, PesananStatus::Terverifikasi, $admin);

            return $pesanan;
        });
    }

    public function tolak(Pesanan $pesanan, User $admin, string $alasan): Pesanan
    {
        $this->wajibPeran($admin, Role::Admin);
        $alasan = $this->wajibAlasan($alasan);

        return DB::transaction(function () use ($pesanan, $admin, $alasan) {
            $pesanan = $this->kunci($pesanan);
            $this->wajibTransisi($pesanan, PesananStatus::Ditolak);

            $pesanan->alasan_penolakan = $alasan;
            $this->pindah($pesanan, PesananStatus::Ditolak, $admin, $alasan);

            return $pesanan;
        });
    }

    public function batalkanOlehWarga(Pesanan $pesanan, User $warga, ?string $alasan = null): Pesanan
    {
        $this->wajibPeran($warga, Role::Warga);

        return DB::transaction(function () use ($pesanan, $warga, $alasan) {
            $pesanan = $this->kunci($pesanan);
            if ($pesanan->user_id !== $warga->id) {
                throw new AuthorizationException('Pesanan ini bukan milik Anda.');
            }
            if (! in_array($pesanan->status, [PesananStatus::Menunggu, PesananStatus::Terverifikasi], true)) {
                throw new TransisiTidakValidException('Pesanan tidak dapat dibatalkan setelah ambulans ditugaskan.');
            }

            $alasan = $this->bersihkan($alasan);
            $pesanan->alasan_pembatalan = $alasan;
            $this->pindah($pesanan, PesananStatus::Dibatalkan, $warga, $alasan);

            return $pesanan;
        });
    }

    public function tugaskan(Pesanan $pesanan, User $admin, Ambulans $ambulans, User $sopir): Pesanan
    {
        $this->wajibPeran($admin, Role::Admin);

        return DB::transaction(function () use ($pesanan, $admin, $ambulans, $sopir) {
            $pesanan = $this->kunci($pesanan);
            $this->wajibTransisi($pesanan, PesananStatus::Ditugaskan);

            $ambulans = Ambulans::query()->lockForUpdate()->findOrFail($ambulans->id);
            $sopir = User::query()->lockForUpdate()->findOrFail($sopir->id);
            $this->periksaSumberDaya($pesanan, $ambulans, $sopir);

            $pesanan->ambulans_id = $ambulans->id;
            $pesanan->sopir_id = $sopir->id;
            $pesanan->ditugaskan_pada = now();
            $ambulans->status = StatusAmbulans::Bertugas;
            $ambulans->save();

            $this->pindah($pesanan, PesananStatus::Ditugaskan, $admin, $ambulans->plat_nomor.' dengan '.$sopir->name);

            return $pesanan;
        });
    }

    public function terima(Pesanan $pesanan, User $sopir): Pesanan
    {
        return DB::transaction(function () use ($pesanan, $sopir) {
            $pesanan = $this->kunciUntukSopir($pesanan, $sopir);
            $this->wajibTransisi($pesanan, PesananStatus::Diterima);

            $pesanan->diterima_pada = now();
            $this->pindah($pesanan, PesananStatus::Diterima, $sopir);

            return $pesanan;
        });
    }

    public function tolakPenugasan(Pesanan $pesanan, User $sopir, string $alasan): Pesanan
    {
        $alasan = $this->wajibAlasan($alasan);

        return DB::transaction(function () use ($pesanan, $sopir, $alasan) {
            $pesanan = $this->kunciUntukSopir($pesanan, $sopir);
            if ($pesanan->status !== PesananStatus::Ditugaskan) {
                throw new TransisiTidakValidException('Penugasan hanya dapat ditolak sebelum diterima.');
            }

            $this->lepasSumberDaya($pesanan);
            $this->pindah($pesanan, PesananStatus::Terverifikasi, $sopir, 'Ditolak sopir: '.$alasan);

            return $pesanan;
        });
    }

    public function lepaskanPenugasan(Pesanan $pesanan, User $admin, ?string $alasan = null): Pesanan
    {
        $this->wajibPeran($admin, Role::Admin);

        return DB::transaction(function () use ($pesanan, $admin, $alasan) {
            $pesanan = $this->kunci($pesanan);
            if ($pesanan->status !== PesananStatus::Ditugaskan) {
                throw new TransisiTidakValidException('Penugasan hanya dapat dilepas sebelum diterima sopir.');
            }

            $this->lepasSumberDaya($pesanan);
            $this->pindah($pesanan, PesananStatus::Terverifikasi, $admin, $this->bersihkan($alasan) ?? 'Penugasan dilepas Dinkes');

            return $pesanan;
        });
    }

    public function mulaiPerjalanan(Pesanan $pesanan, User $sopir): Pesanan
    {
        return DB::transaction(function () use ($pesanan, $sopir) {
            $pesanan = $this->kunciUntukSopir($pesanan, $sopir);
            $this->wajibTransisi($pesanan, PesananStatus::DalamPerjalanan);

            $pesanan->berangkat_pada = now();
            $this->pindah($pesanan, PesananStatus::DalamPerjalanan, $sopir);

            return $pesanan;
        });
    }

    public function selesaikan(Pesanan $pesanan, User $sopir): Pesanan
    {
        return DB::transaction(function () use ($pesanan, $sopir) {
            $pesanan = $this->kunciUntukSopir($pesanan, $sopir);
            $this->wajibTransisi($pesanan, PesananStatus::Selesai);

            $pesanan->selesai_pada = now();
            $this->bebaskanAmbulans($pesanan);
            $this->pindah($pesanan, PesananStatus::Selesai, $sopir);

            return $pesanan;
        });
    }

    public function batalkanOlehAdmin(Pesanan $pesanan, User $admin, string $alasan): Pesanan
    {
        $this->wajibPeran($admin, Role::Admin);
        $alasan = $this->wajibAlasan($alasan);

        return DB::transaction(function () use ($pesanan, $admin, $alasan) {
            $pesanan = $this->kunci($pesanan);
            if ($pesanan->status === PesananStatus::Menunggu) {
                throw new TransisiTidakValidException('Pesanan yang menunggu verifikasi ditolak, bukan dibatalkan.');
            }
            $this->wajibTransisi($pesanan, PesananStatus::Dibatalkan);

            $this->bebaskanAmbulans($pesanan);
            $pesanan->alasan_pembatalan = $alasan;
            $this->pindah($pesanan, PesananStatus::Dibatalkan, $admin, $alasan);

            return $pesanan;
        });
    }

    private function kunci(Pesanan $pesanan): Pesanan
    {
        return Pesanan::query()->lockForUpdate()->findOrFail($pesanan->id);
    }

    private function kunciUntukSopir(Pesanan $pesanan, User $sopir): Pesanan
    {
        $this->wajibPeran($sopir, Role::Sopir);
        $pesanan = $this->kunci($pesanan);
        if ($pesanan->sopir_id === null || $pesanan->sopir_id !== $sopir->id) {
            throw new AuthorizationException('Pesanan ini tidak ditugaskan kepada Anda.');
        }

        return $pesanan;
    }

    private function wajibPeran(User $pelaku, Role $peran): void
    {
        if ($pelaku->role !== $peran) {
            throw new AuthorizationException('Aksi ini hanya untuk '.$peran->label().'.');
        }
    }

    private function wajibTransisi(Pesanan $pesanan, PesananStatus $ke): void
    {
        if (! $pesanan->status->bolehKe($ke)) {
            throw new TransisiTidakValidException(
                'Pesanan berstatus '.$pesanan->status->label().' tidak dapat diubah menjadi '.$ke->label().'.'
            );
        }
    }

    private function wajibAlasan(string $alasan): string
    {
        $alasan = trim($alasan);
        if ($alasan === '') {
            throw new TransisiTidakValidException('Alasan wajib diisi.');
        }

        return $alasan;
    }

    private function bersihkan(?string $teks): ?string
    {
        $teks = $teks === null ? null : trim($teks);

        return $teks === '' ? null : $teks;
    }

    private function periksaSumberDaya(Pesanan $pesanan, Ambulans $ambulans, User $sopir): void
    {
        if ($ambulans->status !== StatusAmbulans::Tersedia) {
            throw new PenugasanTidakValidException('Ambulans '.$ambulans->plat_nomor.' tidak tersedia.');
        }
        if (! Faskes::query()->whereKey($ambulans->faskes_id)->where('aktif', true)->exists()) {
            throw new PenugasanTidakValidException('Faskes ambulans tidak aktif.');
        }
        if ($sopir->role !== Role::Sopir || ! $sopir->aktif) {
            throw new PenugasanTidakValidException('Pengguna yang dipilih bukan sopir aktif.');
        }
        if (! $sopir->sedang_online) {
            throw new PenugasanTidakValidException('Sopir '.$sopir->name.' sedang offline.');
        }
        if ($sopir->faskes_id === null || $sopir->faskes_id !== $ambulans->faskes_id) {
            throw new PenugasanTidakValidException('Sopir harus berasal dari Faskes yang sama dengan ambulans.');
        }

        $aktif = array_column(array_filter(PesananStatus::cases(), fn (PesananStatus $s) => $s->memegangSumberDaya()), 'value');
        $dipakai = fn (string $kolom, int $id) => Pesanan::query()
            ->where($kolom, $id)
            ->whereIn('status', $aktif)
            ->whereKeyNot($pesanan->id)
            ->exists();

        if ($dipakai('sopir_id', $sopir->id)) {
            throw new PenugasanTidakValidException('Sopir '.$sopir->name.' masih memegang pesanan lain.');
        }
        if ($dipakai('ambulans_id', $ambulans->id)) {
            throw new PenugasanTidakValidException('Ambulans '.$ambulans->plat_nomor.' masih dipakai pesanan lain.');
        }
    }

    /** Kosongkan penugasan dan bebaskan ambulans (dipakai saat sopir menolak atau Dinkes melepas). */
    private function lepasSumberDaya(Pesanan $pesanan): void
    {
        $this->bebaskanAmbulans($pesanan);
        $pesanan->ambulans_id = null;
        $pesanan->sopir_id = null;
        $pesanan->ditugaskan_pada = null;
    }

    private function bebaskanAmbulans(Pesanan $pesanan): void
    {
        if ($pesanan->ambulans_id === null) {
            return;
        }

        $ambulans = Ambulans::query()->lockForUpdate()->find($pesanan->ambulans_id);
        if ($ambulans !== null && $ambulans->status === StatusAmbulans::Bertugas) {
            $ambulans->status = StatusAmbulans::Tersedia;
            $ambulans->save();
        }
    }

    private function pindah(Pesanan $pesanan, PesananStatus $ke, User $pelaku, ?string $catatan = null): void
    {
        $dari = $pesanan->status;
        $pesanan->status = $ke;
        $pesanan->save();

        $this->catat($pesanan, $dari, $ke, $pelaku, $catatan);
    }

    private function catat(Pesanan $pesanan, ?PesananStatus $dari, PesananStatus $ke, User $pelaku, ?string $catatan = null): void
    {
        PesananLog::create([
            'pesanan_id' => $pesanan->id,
            'dari_status' => $dari,
            'ke_status' => $ke,
            'oleh_id' => $pelaku->id,
            'peran' => $pelaku->role,
            'catatan' => $catatan,
        ]);
    }
}
