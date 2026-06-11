@extends('layouts.master')
@section('title', 'Kebenaran')
@section('crumb', 'Sistem')

@section('content')
<div class="page-head">
    <div><h1>Kebenaran</h1><p class="lead">Senarai kebenaran sistem yang boleh ditetapkan kepada peranan.</p></div>
    <a href="{{ route('permissions.create') }}" class="btn btn-gold">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Kebenaran
    </a>
</div>

<div class="panel">
    <div class="panel-head"><h3>Senarai Kebenaran</h3><span class="badge">{{ $permissions->count() }}</span></div>
    <table>
        <thead><tr><th>Nama</th><th>Slug</th><th>Penerangan</th><th style="text-align:right;">Tindakan</th></tr></thead>
        <tbody>
            @forelse ($permissions as $p)
                <tr>
                    <td style="font-weight:600;">{{ $p->name }}</td>
                    <td><span class="badge gold">{{ $p->slug }}</span></td>
                    <td class="cell-sub">{{ $p->description ?? '—' }}</td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;">
                            <a href="{{ route('permissions.edit', $p) }}" class="btn btn-ghost btn-sm">Sunting</a>
                            <form method="POST" action="{{ route('permissions.destroy', $p) }}" data-confirm="Padam kebenaran ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Padam</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty">Tiada kebenaran lagi.</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
