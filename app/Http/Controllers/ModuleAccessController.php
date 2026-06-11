<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleAccessController extends Controller
{
    /**
     * Papar matrix: baris = peranan, lajur = modul.
     */
    public function index()
    {
        $modules = config('modules.modules');

        // Semua peranan kecuali super-user (super-user sentiasa penuh, tak perlu ditunjuk)
        $roles = Role::where('slug', '!=', 'super-user')->orderBy('id')->get();

        // Akses semasa: [role_id => [module_key => true]]
        $current = [];
        foreach (DB::table('module_role')->get() as $row) {
            $current[$row->role_id][$row->module_key] = true;
        }

        return view('tetapan.modul', compact('modules', 'roles', 'current'));
    }

    /**
     * Simpan matrix. Borang hantar: access[role_id][] = module_key
     */
    public function update(Request $request)
    {
        $validKeys = array_keys(config('modules.modules'));
        $access    = $request->input('access', []); // [role_id => [module_key,...]]

        $roles = Role::where('slug', '!=', 'super-user')->pluck('id');

        DB::transaction(function () use ($access, $validKeys, $roles) {
            // Bersihkan akses sedia ada untuk peranan yang dikawal
            DB::table('module_role')->whereIn('role_id', $roles)->delete();

            $rows = [];
            foreach ($access as $roleId => $keys) {
                if (! $roles->contains((int) $roleId)) {
                    continue; // abai role_id yang tidak sah / super-user
                }
                foreach ((array) $keys as $key) {
                    if (in_array($key, $validKeys, true)) {
                        $rows[] = ['role_id' => (int) $roleId, 'module_key' => $key];
                    }
                }
            }

            if (! empty($rows)) {
                DB::table('module_role')->insert($rows);
            }
        });

        return redirect()->route('tetapan.modul')
            ->with('success', 'Akses modul berjaya dikemaskini.');
    }
}
