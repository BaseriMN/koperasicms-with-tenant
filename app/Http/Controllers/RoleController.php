<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();
        
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:50', 'unique:roles,name'],
            'description'   => ['nullable', 'string'],
            'permissions'   => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role berjaya ditambah.');
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
            'description'   => ['nullable', 'string'],
            'permissions'   => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role berjaya dikemaskini.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'super-user') {
            return redirect()->route('roles.index')
                ->with('error', 'Role Super User tidak boleh dipadam.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role berjaya dipadam.');
    }
}
