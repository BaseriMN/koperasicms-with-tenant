<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NextOfKin extends Model
{
    protected $table = 'next_of_kin';

    protected $fillable = [
        'member_id', 'nama', 'no_kp', 'hubungan', 'telefon', 'alamat',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
