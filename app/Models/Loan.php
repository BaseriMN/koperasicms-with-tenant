<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'member_id', 'dimohon_oleh', 'amount', 'tempoh', 'tujuan',
        'status', 'catatan', 'reviewed_by', 'reviewed_at',
        'meeting_id', 'pencadang_id', 'penyokong_id',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dimohon_oleh');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function pencadang(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'pencadang_id');
    }

    public function penyokong(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'penyokong_id');
    }
}

