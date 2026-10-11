<?php

namespace App\Enums\Concerns;

trait PunyaOpsi
{
    /** @return array<string, string> nilai => label, untuk <x-ui.pilihan>. */
    public static function opsi(): array
    {
        $hasil = [];
        foreach (self::cases() as $kasus) {
            $hasil[$kasus->value] = $kasus->label();
        }

        return $hasil;
    }
}
