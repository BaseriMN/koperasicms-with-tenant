@extends('layouts.master')
@section('title', 'Simpanan & Saham')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div><h1>Simpanan &amp; Saham</h1><p class="lead">Rekod dan urus transaksi simpanan serta saham ahli.</p></div>
    <a href="{{ route('simpanan.create') }}" class="btn btn-gold">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Rekod Transaksi
    </a>
</div>
<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="stat"><div class="k">Jumlah Simpanan</div><div class="v" style="font-size:28px;">RM {{ number_format($stats['simpanan'], 2) }}</div><div class="meta">▲ keseluruhan</div></div>
    <div class="stat"><div class="k">Jumlah Saham</div><div class="v" style="font-size:28px;">RM {{ number_format($stats['saham'], 2) }}</div><div class="meta">▲ keseluruhan</div></div>
</div>
<div class="panel">
    <div class="panel-head"><h3>Transaksi Terkini</h3><span class="badge">{{ $savings->total() }} rekod</span></div>
    <table>
        <thead><tr><th>Ahli</th><th>Jenis</th><th>Amaun</th><th>Tarikh</th></tr></thead>
        <tbody>
            @forelse ($savings as $s)
                <tr>
                    <td><div class="cell-main"><div class="av">{{ strtoupper(substr($s->user->name ?? '?', 0, 1)) }}</div><div style="font-weight:600;">{{ $s->user->name ?? '—' }}</div></div></td>
                    <td>@if($s->jenis==='saham')<span class="badge gold">Saham</span>@else<span class="badge teal">Simpanan</span>@endif</td>
                    <td style="font-weight:600;">RM {{ number_format($s->amaun, 2) }}</td>
                    <td class="cell-sub">{{ $s->created_at?->translatedFormat('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3v18M7 8h7a3 3 0 010 6H6"/></svg>
                    <div>Tiada transaksi lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $savings->links() }}</div>
@endsection
