@extends('layouts.master')
@section('title', 'Peranan')
@section('crumb', 'Peranan')

@section('content')
<div class="page-head">
    <div><h1>{{ $role->name }}</h1><p class="lead">{{ $role->description ?? '—' }}</p></div>
    <a href="{{ route('roles.edit', $role) }}" class="btn btn-gold">Sunting</a>
</div>
<div class="grid grid-2">
    <div class="panel"><div class="panel-head"><h3>Kebenaran</h3></div>
        <div class="panel-body" style="display:flex;gap:8px;flex-wrap:wrap;">
            @forelse ($role->permissions as $p)<span class="badge gold">{{ $p->name }}</span>
            @empty<span class="cell-sub">Tiada kebenaran.</span>@endforelse
        </div>
    </div>
    <div class="panel"><div class="panel-head"><h3>Ahli ({{ $role->users->count() }})</h3></div>
        <div class="panel-body" style="display:flex;gap:8px;flex-wrap:wrap;">
            @forelse ($role->users as $u)<span class="badge teal">{{ $u->name }}</span>
            @empty<span class="cell-sub">Tiada ahli.</span>@endforelse
        </div>
    </div>
</div>
@endsection
