@extends('layouts.master')
@section('title', 'Profil Saya')
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Profil Saya</h1><p class="lead">Urus maklumat peribadi & kata laluan anda.</p></div>
</div>

<div class="grid grid-2" style="align-items:start;">
    {{-- Maklumat + avatar --}}
    <div class="panel">
        <div class="panel-head"><h3>Maklumat Peribadi</h3></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div style="display:flex;gap:18px;align-items:center;margin-bottom:20px;">
                    <div style="width:74px;height:74px;border-radius:50%;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,var(--teal),var(--teal-deep));display:grid;place-items:center;color:#fff;font-size:28px;font-weight:600;">
                        @if ($user->avatar_path)
                            <img src="{{ tenant_asset($user->avatar_path) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:13px;font-weight:600;margin-bottom:7px;">Foto Profil <span class="hint">(PNG/JPG, maks 2MB)</span></label>
                        <input class="input" type="file" name="avatar" accept="image/*">
                        @if ($user->avatar_path)
                            <button form="form-buang-avatar" type="submit" style="margin-top:8px;background:none;border:none;color:var(--danger);font-size:12px;cursor:pointer;text-decoration:underline;">Buang foto</button>
                        @endif
                    </div>
                </div>
                @error('avatar') <div class="err" style="margin-top:-10px;margin-bottom:14px;">{{ $message }}</div> @enderror

                <div class="field">
                    <label>Nama</label>
                    <input class="input" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Emel</label>
                    <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field" style="margin-bottom:0;">
                    <label>Telefon</label>
                    <input class="input" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="01x-xxxxxxx">
                    @error('phone') <div class="err">{{ $message }}</div> @enderror
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tukar kata laluan --}}
    <div class="panel">
        <div class="panel-head"><h3>Tukar Kata Laluan</h3></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('profil.password') }}">
                @csrf @method('PUT')
                <div class="field">
                    <label>Kata Laluan Semasa</label>
                    <input class="input" type="password" name="current_password" required>
                    @error('current_password') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Kata Laluan Baharu</label>
                    <input class="input" type="password" name="password" required>
                    @error('password') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field" style="margin-bottom:0;">
                    <label>Sahkan Kata Laluan Baharu</label>
                    <input class="input" type="password" name="password_confirmation" required>
                </div>
                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">Tukar Kata Laluan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Borang berasingan buang avatar --}}
<form id="form-buang-avatar" method="POST" action="{{ route('profil.avatar.buang') }}" data-confirm="Buang foto profil?">
    @csrf @method('DELETE')
</form>
@endsection