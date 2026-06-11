@extends('layouts.master')
@section('title', 'Sunting Ahli')
@section('crumb', 'Pengurusan Ahli')

@section('content')
<div class="page-head">
    <div><h1>Sunting Ahli</h1><p class="lead">Kemaskini maklumat dan peranan {{ $user->name }}.</p></div>
    <a href="{{ route('users.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="panel" style="max-width:720px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')
            <div class="grid grid-2">
                <div class="field">
                    <label>Nama Penuh</label>
                    <input class="input" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Email</label>
                    <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Telefon</label>
                    <input class="input" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="field">
                    <label>Status</label>
                    <select class="select" name="is_active">
                        <option value="1" {{ $user->is_active ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ ! $user->is_active ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="field">
                    <label>Kata Laluan Baharu <span class="hint">(biar kosong jika tiada perubahan)</span></label>
                    <input class="input" type="password" name="password">
                    @error('password') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Sahkan Kata Laluan</label>
                    <input class="input" type="password" name="password_confirmation">
                </div>
            </div>

            <div class="field">
                <label>Peranan</label>
                <div class="check-grid">
                    @php $assigned = $user->roles->pluck('id')->toArray(); @endphp
                    @foreach ($roles as $role)
                        <label class="check">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                {{ in_array($role->id, old('roles', $assigned)) ? 'checked' : '' }}>
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Kemaskini</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
