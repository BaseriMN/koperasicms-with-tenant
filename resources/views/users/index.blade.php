@extends('layouts.master')
@section('title', 'Pengurusan Staff')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div>
        <h1>Pengurusan Staff</h1>
        <p class="lead">Senarai semua staff koperasi dan peranan masing-masing.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-gold">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Staff
    </a>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-body" style="padding:16px 22px;">
        <form method="GET" action="{{ route('users.index') }}" style="display:flex;gap:10px;">
            <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email...">
            <button class="btn btn-gold" type="submit">Cari</button>
            @if (request('search') || request('status'))
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3>Senarai Staff</h3>
        <span class="badge">{{ $users->total() }} staff</span>
    </div>
    <table>
        <thead>
            <tr><th>Staff</th><th>Telefon</th><th>Peranan</th><th>Status</th><th style="text-align:right;">Tindakan</th></tr>
        </thead>
        <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>
                        <div class="cell-main">
                            <div class="av">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:600;">{{ $u->name }}</div>
                                <div class="cell-sub">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $u->phone ?? '—' }}</td>
                    <td>
                        @forelse ($u->roles as $role)
                            <span class="badge teal">{{ $role->name }}</span>
                        @empty
                            <span class="cell-sub">Tiada peranan</span>
                        @endforelse
                    </td>
                    <td>
                        @if ($u->is_active)
                            <span class="badge ok"><span class="dot"></span>Aktif</span>
                        @else
                            <span class="badge off"><span class="dot"></span>Tidak Aktif</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;">
                            @php $targetSuper = $u->roles->contains('slug', 'super-user'); @endphp

                            {{-- Sunting: kalau target super-user, hanya super-user boleh --}}
                            @if (! $targetSuper || auth()->user()->hasRole('super-user'))
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-ghost btn-sm">Sunting</a>
                            @endif

                            {{-- Padam: super-user TAK BOLEH dipadam sesiapa --}}
                            @if (! $targetSuper)
                                <form method="POST" action="{{ route('users.destroy', $u) }}" data-confirm="Padam ahli {{ $u->name }}?" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Padam</button>
                                </form>
                            @endif

                            {{-- Kalau takde tindakan langsung, tunjuk tanda --}}
                            @if ($targetSuper && ! auth()->user()->hasRole('super-user'))
                                <span class="cell-sub" style="font-size:12px;">Dilindungi</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">
                    <div class="empty">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0111 0"/></svg>
                        <div>Tiada ahli dijumpai.</div>
                    </div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:18px;">{{ $users->links() }}</div>
@endsection
