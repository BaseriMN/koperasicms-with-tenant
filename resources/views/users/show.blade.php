@extends('layouts.master')
@section('title', 'Profil Staff')
@section('crumb', 'Pengurusan Staff')

@section('content')
<div class="page-head">
    <div><h1>{{ $user->name }}</h1><p class="lead">{{ $user->email }}</p></div>
    <a href="{{ route('users.edit', $user) }}" class="btn btn-gold">Sunting</a>
</div>

<div class="grid grid-2">
    <div class="panel">
        <div class="panel-head"><h3>Maklumat</h3></div>
        <div class="panel-body">
            <div class="field"><label>Telefon</label><div>{{ $user->phone ?? '—' }}</div></div>
            <div class="field"><label>Status</label>
                @if ($user->is_active)<span class="badge ok"><span class="dot"></span>Aktif</span>
                @else<span class="badge off"><span class="dot"></span>Tidak Aktif</span>@endif
            </div>
            <div class="field"><label>Didaftar</label><div>{{ $user->created_at?->translatedFormat('d F Y') }}</div></div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>Peranan</h3></div>
        <div class="panel-body" style="display:flex;gap:8px;flex-wrap:wrap;">
            @forelse ($user->roles as $role)
                <span class="badge teal">{{ $role->name }}</span>
            @empty
                <span class="cell-sub">Tiada peranan ditetapkan.</span>
            @endforelse
        </div>
    </div>
</div>
@endsection
