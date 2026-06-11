<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendAllocation extends Model
{
    protected $fillable = [
        'dividend_run_id', 'nama_tabung', 'jenis_kira', 'nilai', 'amaun', 'susunan',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'amaun' => 'decimal:2',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(DividendRun::class, 'dividend_run_id');
    }

    /**
     * Kira amaun peruntukan berdasarkan untung bersih asal.
     * peratus : nilai% daripada untung bersih
     * amaun   : nilai RM tetap
     */
    public function kiraAmaun(float $untungBersih): float
    {
        return $this->jenis_kira === 'peratus'
            ? round($untungBersih * ((float) $this->nilai / 100), 2)
            : (float) $this->nilai;
    }
}
