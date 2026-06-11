<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountEntry extends Model
{
    protected $fillable = [
        'category_id', 'jenis', 'member_id', 'amaun', 'tarikh',
        'rujukan', 'penerima_pembayar', 'keterangan', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amaun'  => 'decimal:2',
            'tarikh' => 'date',
        ];
    }

    // ---- Relationships ----
    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ---- Scopes ----
    public function scopePendapatan(Builder $q): Builder
    {
        return $q->where('jenis', 'pendapatan');
    }

    public function scopePerbelanjaan(Builder $q): Builder
    {
        return $q->where('jenis', 'perbelanjaan');
    }

    public function scopeDalamTempoh(Builder $q, ?string $dari, ?string $hingga): Builder
    {
        return $q->when($dari, fn ($qq) => $qq->whereDate('tarikh', '>=', $dari))
                 ->when($hingga, fn ($qq) => $qq->whereDate('tarikh', '<=', $hingga));
    }
}
