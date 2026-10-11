<?php

namespace Tests\Concerns;

use App\Models\Faskes;
use App\Models\User;

trait MembuatPengguna
{
    protected function buatWarga(array $atribut = []): User
    {
        return User::factory()->warga()->create($atribut);
    }

    protected function buatAdmin(array $atribut = []): User
    {
        return User::factory()->admin()->create($atribut);
    }

    protected function buatPetugas(?Faskes $faskes = null, array $atribut = []): User
    {
        return User::factory()->petugas($faskes)->create($atribut);
    }

    protected function buatSopir(?Faskes $faskes = null, array $atribut = []): User
    {
        return User::factory()->sopir($faskes)->create($atribut);
    }
}
