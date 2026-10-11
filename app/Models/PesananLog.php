<?php

namespace App\Models;

use App\Enums\PesananStatus;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'pesanan_log')]
#[Fillable(['pesanan_id', 'dari_status', 'ke_status', 'oleh_id', 'peran', 'catatan'])]
class PesananLog extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'dari_status' => PesananStatus::class,
            'ke_status' => PesananStatus::class,
            'peran' => Role::class,
            'created_at' => 'datetime',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oleh_id');
    }
}
