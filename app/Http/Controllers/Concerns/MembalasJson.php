<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

/** Konvensi JSON bersama: sukses { ok: true, data }, gagal { ok: false, pesan }. */
trait MembalasJson
{
    protected function ok(mixed $data = [], int $status = 200): JsonResponse
    {
        return response()->json(['ok' => true, 'data' => $data], $status);
    }

    protected function gagal(string $pesan, int $status = 422): JsonResponse
    {
        return response()->json(['ok' => false, 'pesan' => $pesan], $status);
    }
}
