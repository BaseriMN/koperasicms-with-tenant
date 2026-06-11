<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    private const CACHE_KEY = 'settings.all';

    /**
     * Ambil satu tetapan (dengan fallback default).
     */
    public static function get(string $key, $default = null)
    {
        return static::all_cached()[$key] ?? $default;
    }

    /**
     * Simpan / kemaskini satu tetapan.
     */
    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Simpan banyak tetapan sekaligus.
     */
    public static function putMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Semua tetapan sebagai array [key => value], dicache selama-lamanya
     * (cache dibersihkan setiap kali put/putMany).
     */
    public static function all_cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}
