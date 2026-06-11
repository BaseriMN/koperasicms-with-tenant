@extends('layouts.master')
@section('title', 'Peranan (Roles)')
@section('crumb', 'Sistem')

@section('content')
<div class="page-head">
    <div><h1>Peranan</h1><p class="lead">Urus peranan pengguna dan kebenaran berkaitan.</p></div>
    <a href="{{ route('roles.create') }}" class="btn btn-gold">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Peranan
    </a>
</div>

<div class="grid grid-3">
    @forelse ($roles as $role)
        <div class="panel">
            <div class="panel-body">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h3 style="font-family:'Fraunces',serif;font-size:18px;">{{ $role->name }}</h3>
                        <span class="badge gold" style="margin-top:6px;">{{ $role->slug }}</span>
                    </div>
                    <span class="badge teal">{{ $role->users_count }} ahli</span>
                </div>
                <p class="cell-sub" style="margin:14px 0;min-height:38px;">{{ $role->description ?? 'Tiada penerangan.' }}</p>
                <div style="display:flex;gap:8px;">
                    @if ($role->slug !== 'super-user')
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-ghost btn-sm">Sunting</a>
                    
                        <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm="Padam peranan {{ $role->name }}?">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">Padam</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="empty" style="grid-column:1/-1;">Tiada peranan.</div>
    @endforelse
</div>
@endsection
