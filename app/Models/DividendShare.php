<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendShare extends Model
{
    protected $fillable = [
        'dividend_run_id', 'member_id', 'saham_layak', 'saham_auto',
        'peratus', 'amaun_dividen', 'override',
    ];

    protected function casts(): array
    {
        return [
            'saham_layak'   => 'decimal:2',
            'saham_auto'    => 'decimal:2',
            'peratus'       => 'decimal:4',
            'amaun_dividen' => 'decimal:2',
            'override'      => 'boolean',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(DividendRun::class, 'dividend_run_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
