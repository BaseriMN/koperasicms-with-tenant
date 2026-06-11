<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ModuleAccess
{
    /**
     * Senarai key modul yang boleh diakses oleh seseorang pengguna.
     * Super-user sentiasa dapat SEMUA modul (tidak boleh terkunci).
     */
    public static function allowedFor(User $user): array
    {
        $allKeys = array_keys(config('modules.modules'));

        if ($user->hasRole('super-user')) {
            return $allKeys;
        }

        $roleIds = $user->roles->pluck('id');

        if ($roleIds->isEmpty()) {
            return [];
        }

        return DB::table('module_role')
            ->whereIn('role_id', $roleIds)
            ->distinct()
            ->pluck('module_key')
            ->intersect($allKeys)   // abaikan key usang jika config berubah
            ->values()
            ->all();
    }

    /**
     * Adakah pengguna boleh akses satu modul tertentu?
     */
    public static function userCan(User $user, string $moduleKey): bool
    {
        if ($user->hasRole('super-user')) {
            return true;
        }

        return in_array($moduleKey, static::allowedFor($user), true);
    }

    /**
     * Cari module_key daripada nama route semasa (cth 'users.edit' -> 'pengurusan_ahli').
     */
    public static function keyForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (config('modules.modules') as $key => $mod) {
            $prefix = rtrim($mod['route_prefix'], '*');
            if (str_starts_with($routeName, $prefix)) {
                return $key;
            }
        }

        return null;
    }
}
