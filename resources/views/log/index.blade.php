@extends('layouts.master')
@section('title', 'Log Aktiviti')
@section('crumb', 'Sistem')

@section('content')
<div class="page-head">
    <div><h1>Log Aktiviti</h1><p class="lead">Jejak semua proses sistem — siapa buat, bila, dan apa.</p></div>
    <a href="{{ route('log.export.csv', request()->only('dari','hingga','modul')) }}" class="btn btn-ghost">
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12M8 11l4 4 4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
        Export CSV
    </a>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-body" style="padding:16px 22px;">
        <form method="GET" action="{{ route('log.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div class="field" style="max-width:170px;margin:0;">
                <label style="font-size:12px;">Modul</label>
                <select class="select" name="modul">
                    <option value="">Semua modul</option>
                    @foreach (['Transaksi','Pindah Saham','Pindah Keahlian','Pinjaman','Dividen','Akaun','Mesyuarat'] as $mod)
                        <option value="{{ $mod }}" {{ request('modul')===$mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="max-width:160px;margin:0;">
                <label style="font-size:12px;">Dari Tarikh</label>
                <input class="input" type="date" name="dari" value="{{ request('dari') }}">
            </div>
            <div class="field" style="max-width:160px;margin:0;">
                <label style="font-size:12px;">Hingga Tarikh</label>
                <input class="input" type="date" name="hingga" value="{{ request('hingga') }}">
            </div>
            <button class="btn btn-gold" type="submit">Tapis</button>
            @if (request('modul') || request('dari') || request('hingga'))
                <a href="{{ route('log.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Rekod Aktiviti</h3><span class="badge">{{ $log->total() }} rekod</span></div>
    <table>
        <thead><tr><th>Tarikh & Masa</th><th>Modul</th><th>Aktiviti</th><th>Butiran</th><th>Oleh</th></tr></thead>
        <tbody>
            @forelse ($log as $row)
                <tr>
                    <td class="cell-sub">{{ $row['masa']?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                    <td>
                        @php
                            $warna = match($row['modul']) {
                                'Transaksi' => 'teal', 'Pindah Saham' => 'gold', 'Pindah Keahlian' => 'gold',
                                'Pinjaman' => 'ok', 'Dividen' => 'gold', 'Akaun' => 'teal', default => '',
                            };
                        @endphp
                        <span class="badge {{ $warna }}">{{ $row['modul'] }}</span>
                    </td>
                    <td style="font-weight:600;">{{ $row['aktiviti'] }}</td>
                    <td class="cell-sub">{{ $row['butiran'] }}</td>
                    <td>{{ $row['oleh'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>Tiada aktiviti dalam tempoh ini.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $log->links() }}</div>
@endsection