@extends('layouts.master')
@section('title', 'Laporan Audit')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div><h1>Laporan Audit</h1><p class="lead">Semakan rekod kewangan koperasi untuk juruaudit.</p></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('audit.export.csv') }}" class="btn btn-ghost">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12M8 11l4 4 4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            Export CSV
        </a>
        <button class="btn btn-ghost" onclick="window.print()">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-4a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
            Cetak
        </button>
    </div>
</div>
<div class="grid grid-3" style="margin-bottom:24px;">
    @if (simpanan_aktif())
    <div class="stat"><div class="k">Baki Simpanan</div><div class="v" style="font-size:24px;">RM {{ number_format($stats['simpanan'], 0) }}</div><div class="meta">keseluruhan</div></div>
    @endif
    <div class="stat"><div class="k">Baki Saham</div><div class="v" style="font-size:24px;">RM {{ number_format($stats['saham'], 0) }}</div><div class="meta">keseluruhan</div></div>
    @if (pinjaman_aktif())
    <div class="stat"><div class="k">Pinjaman Diluluskan</div><div class="v" style="font-size:24px;">RM {{ number_format($stats['pinjaman_lulus'], 0) }}</div><div class="meta">{{ $stats['pinjaman_pending'] }} menunggu</div></div>
    @endif
</div>
<div class="panel">
    <div class="panel-head"><h3>Lejar Terkini</h3><span class="badge gold">{{ $stats['rekod_transaksi'] }} rekod transaksi</span></div>
    <table>
        <thead><tr><th>Tarikh</th><th>Ahli</th><th>Jenis</th><th>Arah</th><th>Amaun</th><th>Baki</th></tr></thead>
        <tbody>
            @forelse ($records as $t)
                <tr>
                    <td class="cell-sub">{{ $t->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $t->member->no_ahli ?? '—' }}</div>
                        <div class="cell-sub">{{ $t->member->nama ?? '' }}</div>
                    </td>
                    <td>@if($t->jenis==='saham')<span class="badge gold">Saham</span>@else<span class="badge teal">Simpanan</span>@endif</td>
                    <td>@if($t->arah==='masuk')<span class="badge ok">Masuk</span>@else<span class="badge off">Keluar</span>@endif</td>
                    <td style="font-weight:600;">RM {{ number_format($t->amaun, 2) }}</td>
                    <td class="cell-sub">RM {{ number_format($t->baki, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h12l4 4v12H4z"/><path d="M8 13l2.5 2.5L16 10"/></svg>
                    <div>Tiada rekod kewangan untuk diaudit.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
