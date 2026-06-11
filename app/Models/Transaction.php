<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'member_id', 'jenis', 'arah', 'amaun', 'baki',
        'sumber', 'rujukan', 'keterangan', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amaun' => 'decimal:2',
            'baki'  => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
