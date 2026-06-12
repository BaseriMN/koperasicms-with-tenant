@extends('layouts.master')
@section('title', 'Keahlian')
@section('crumb', 'Pengurusan Ahli')

@section('content')
<div class="page-head">
    <div>
        <h1>Keahlian Koperasi</h1>
        <p class="lead">Senarai ahli dengan nombor keahlian, pemilik semasa & status.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="{{ route('members.export.csv') }}" class="btn btn-ghost">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12M8 11l4 4 4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            Export Semua
        </a>
        <a href="{{ route('members.export.csv', request()->only('search', 'status')) }}" class="btn btn-ghost">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 4h18M6 8h12M10 12h4M11 16h2"/></svg>
            Export (Tapisan)
        </a>
        <a href="{{ route('members.create') }}" class="btn btn-gold">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Daftar Ahli
    </a>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-body" style="padding:16px 22px;">
        <form method="GET" action="{{ route('members.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Cari no ahli, nama, atau no KP..." style="flex:1;min-width:220px;">
            <select class="select" name="status" style="max-width:180px;">
                <option value="">Semua status</option>
                <option value="aktif" {{ request('status')==='aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="tidak_aktif" {{ request('status')==='tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                <option value="berhenti" {{ request('status')==='berhenti' ? 'selected' : '' }}>Berhenti</option>
            </select>
            <button class="btn btn-gold" type="submit">Cari</button>
            @if (request('search') || request('status'))
                <a href="{{ route('members.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3>Senarai Ahli</h3>
        <span class="badge">{{ $members->total() }} ahli</span>
    </div>
    <table>
        <thead>
            <tr><th>No. Ahli</th><th>Nama / Pemilik</th><th>Telefon</th><th>Saham</th>@if (simpanan_aktif())<th>Simpanan</th>@endif<th>Status</th><th style="text-align:right;">Tindakan</th></tr>
        </thead>
        <tbody>
            @forelse ($members as $m)
                <tr>
                    <td><span class="badge gold" style="font-family:'Fraunces',serif;font-size:13px;">{{ $m->no_ahli }}</span></td>
                    <td>
                        <div class="cell-main">
                            <div class="av" style="overflow:hidden;">
                                @if ($m->foto_path)
                                    <img src="{{ tenant_asset($m->foto_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($m->nama, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:600;">{{ $m->nama }}</div>
                                <div class="cell-sub">{{ $m->no_kp ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $m->telefon ?? '—' }}</td>
                    <td style="font-weight:600;">RM {{ number_format($m->bakiSaham(), 2) }}</td>
                    @if (simpanan_aktif())
                    <td style="font-weight:600;">RM {{ number_format($m->bakiSimpanan(), 2) }}</td>
                    @endif
                    <td>
                        @if ($m->status === 'aktif')<span class="badge ok"><span class="dot"></span>Aktif</span>
                        @elseif ($m->status === 'tidak_aktif')<span class="badge gold"><span class="dot"></span>Tidak Aktif</span>
                        @else<span class="badge off"><span class="dot"></span>Berhenti</span>@endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;">
                            <a href="{{ route('members.show', $m) }}" class="btn btn-ghost btn-sm">Lihat</a>
                            <a href="{{ route('members.edit', $m) }}" class="btn btn-ghost btn-sm">Sunting</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">
                    <div class="empty">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0111 0"/></svg>
                        <div>Tiada ahli dijumpai.</div>
                    </div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:18px;">{{ $members->links() }}</div>
@endsection
