<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', 'unique:permissions,name'],
            'description' => ['nullable', 'string'],
        ]);

        Permission::create([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berjaya ditambah.');
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'description' => ['nullable', 'string'],
        ]);

        $permission->update([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berjaya dikemaskini.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berjaya dipadam.');
    }
}
