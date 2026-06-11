<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles'    => ['array'],
            'roles.*'  => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'password'          => Hash::make($data['password']),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('users.index')
            ->with('success', 'Ahli berjaya ditambah.');
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Hanya super-user boleh sunting super-user lain
        if ($user->hasRole('super-user') && ! auth()->user()->hasRole('super-user')) {
            return redirect()->route('users.index')
                ->with('error', 'Hanya Super User boleh menyunting Super User.');
        }

        $roles = Role::all();
        $user->load('roles');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Hanya super-user boleh kemaskini super-user lain
        if ($user->hasRole('super-user') && ! auth()->user()->hasRole('super-user')) {
            return redirect()->route('users.index')
                ->with('error', 'Hanya Super User boleh menyunting Super User.');
        }

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'roles'     => ['array'],
            'roles.*'   => ['exists:roles,id'],
        ]);

        $user->name      = $data['name'];
        $user->email     = $data['email'];
        $user->phone     = $data['phone'] ?? null;
        $user->is_active = $request->boolean('is_active');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('users.index')
            ->with('success', 'Maklumat staff berjaya dikemaskini.');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('super-user')) {
            return redirect()->route('users.index')
                ->with('error', 'Super User tidak boleh dipadam.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Staff berjaya dipadam.');
    }
}
