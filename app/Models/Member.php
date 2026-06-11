<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $fillable = [
        'no_ahli', 'user_id', 'nama', 'no_kp', 'foto_path',
        'telefon', 'alamat', 'tarikh_sertai', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_sertai' => 'date',
        ];
    }

    /**
     * Auto-jana nombor ahli AXXXX semasa cipta jika tidak diberikan.
     */
    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (empty($member->no_ahli)) {
                $member->no_ahli = static::nextNoAhli();
            }
        });
    }

    /**
     * Hasilkan nombor ahli seterusnya: A0001, A0002, ...
     * Guna pengiraan PHP (bukan SQL CAST) supaya serasi MySQL & PostgreSQL.
     */
    public static function nextNoAhli(): string
    {
        $max = static::where('no_ahli', 'like', 'A%')
            ->get(['no_ahli'])
            ->map(fn ($m) => (int) substr($m->no_ahli, 1))
            ->max();

        $num = ($max ?? 0) + 1;

        return 'A' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    // ---- Relationships ----
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nextOfKin(): HasOne
    {
        return $this->hasOne(NextOfKin::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function ownershipTransfers(): HasMany
    {
        return $this->hasMany(OwnershipTransfer::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Ahli layak memohon pinjaman jika statusnya aktif.
     */
    public function layakPinjam(): bool
    {
        return $this->status === 'aktif';
    }

    // ---- Baki dikira dari lejar ----
    public function bakiSaham(): float
    {
        return $this->bakiJenis('saham');
    }

    public function bakiSimpanan(): float
    {
        return $this->bakiJenis('simpanan');
    }

    public function bakiJenis(string $jenis): float
    {
        $masuk = (float) $this->transactions()->where('jenis', $jenis)->where('arah', 'masuk')->sum('amaun');
        $keluar = (float) $this->transactions()->where('jenis', $jenis)->where('arah', 'keluar')->sum('amaun');

        return wang($masuk - $keluar);
    }
}
