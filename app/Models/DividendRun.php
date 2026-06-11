<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DividendRun extends Model
{
    protected $fillable = [
        'tahun', 'tarikh_mula', 'tarikh_cutoff', 'untung_bersih',
        'jumlah_saham_anggota', 'peratus_auditor', 'peratus_diluluskan',
        'jumlah_peruntukan', 'untung_boleh_agih', 'peratus_dividen',
        'jumlah_dividen', 'baki_dibawa_hadapan',
        'status', 'tarikh_muktamad', 'dikira_oleh', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_mula'       => 'date',
            'tarikh_cutoff'     => 'date',
            'tarikh_muktamad'   => 'date',
            'untung_bersih'        => 'decimal:2',
            'jumlah_saham_anggota' => 'decimal:2',
            'peratus_auditor'      => 'decimal:2',
            'peratus_diluluskan'   => 'decimal:2',
            'jumlah_peruntukan'    => 'decimal:2',
            'untung_boleh_agih'    => 'decimal:2',
            'peratus_dividen'      => 'decimal:2',
            'jumlah_dividen'       => 'decimal:2',
            'baki_dibawa_hadapan'  => 'decimal:2',
        ];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(DividendAllocation::class)->orderBy('susunan')->orderBy('id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DividendShare::class);
    }

    public function pengira(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikira_oleh');
    }

    public function isDraf(): bool
    {
        return $this->status === 'draf';
    }

    public function isMuktamad(): bool
    {
        return $this->status === 'dimuktamadkan';
    }
}
