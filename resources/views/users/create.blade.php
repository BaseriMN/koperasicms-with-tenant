@extends('layouts.master')
@section('title', 'Tambah Pekerja')
@section('crumb', 'Pengurusan Pekerja')

@section('content')
<div class="page-head">
    <div><h1>Tambah Pekerja Baharu</h1><p class="lead">Daftar pekerja koperasi dan tetapkan peranan.</p></div>
    <a href="{{ route('users.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="panel" style="max-width:720px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="grid grid-2">
                <div class="field">
                    <label>Nama Penuh</label>
                    <input class="input" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Email</label>
                    <input class="input" type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Telefon</label>
                    <input class="input" name="phone" value="{{ old('phone') }}" placeholder="012-3456789">
                    @error('phone') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field"></div>
                <div class="field">
                    <label>Kata Laluan</label>
                    <input class="input" type="password" name="password" required>
                    @error('password') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Sahkan Kata Laluan</label>
                    <input class="input" type="password" name="password_confirmation" required>
                </div>
            </div>

            <div class="field">
                <label>Peranan</label>
                <div class="check-grid">
                    @foreach ($roles as $role)
                        <label class="check">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
                @error('roles') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Simpan Pekerja</button>
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
