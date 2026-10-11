<?php

namespace App\Policies;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Pesanan;
use App\Models\User;

class PesananPolicy
{
    /** Melihat detail pesanan sesuai isolasi data tiap peran (RANCANGAN bagian 3). */
    public function view(User $user, Pesanan $pesanan): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Warga => $pesanan->user_id === $user->id,
            Role::Sopir => $pesanan->sopir_id !== null && $pesanan->sopir_id === $user->id,
            Role::PetugasFaskes => $user->faskes_id !== null && $this->terkaitFaskes($pesanan, $user->faskes_id),
        };
    }

    /** Warga membatalkan pesanannya sendiri sebelum penugasan. */
    public function batalkan(User $user, Pesanan $pesanan): bool
    {
        return $user->role === Role::Warga
            && $pesanan->user_id === $user->id
            && in_array($pesanan->status, [PesananStatus::Menunggu, PesananStatus::Terverifikasi], true);
    }

    private function terkaitFaskes(Pesanan $pesanan, int $faskesId): bool
    {
        if ($pesanan->faskes_tujuan_id === $faskesId) {
            return true;
        }

        return $pesanan->ambulans !== null && $pesanan->ambulans->faskes_id === $faskesId;
    }
}
