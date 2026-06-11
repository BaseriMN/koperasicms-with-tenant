@extends('layouts.master')
@section('title', 'Dividen')
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Pembahagian Dividen</h1><p class="lead">Pengiraan & agihan dividen tahunan kepada ahli.</p></div>
    <a href="{{ route('akaun.dividen.create') }}" class="btn btn-gold">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Pengiraan Baharu
    </a>
</div>

<div class="panel">
    <div class="panel-head"><h3>Senarai Pengiraan</h3><span class="badge">{{ $runs->total() }} rekod</span></div>
    <table>
        <thead>
            <tr><th>Tahun</th><th>Untung Bersih</th><th>Untung Boleh Agih</th><th>Jumlah Dividen</th><th>Ahli</th><th>Status</th><th style="text-align:right;">Tindakan</th></tr>
        </thead>
        <tbody>
            @forelse ($runs as $run)
                <tr>
                    <td><span class="badge gold" style="font-family:'Fraunces',serif;font-size:14px;">{{ $run->tahun }}</span></td>
                    <td>RM {{ number_format($run->untung_bersih, 2) }}</td>
                    <td>RM {{ number_format($run->untung_boleh_agih, 2) }}</td>
                    <td style="font-weight:600;">RM {{ number_format($run->jumlah_dividen, 2) }}</td>
                    <td class="cell-sub">{{ $run->shares_count }}</td>
                    <td>
                        @if ($run->isMuktamad())
                            <span class="badge ok"><span class="dot"></span>Dimuktamadkan</span>
                        @else
                            <span class="badge gold"><span class="dot"></span>Draf</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('akaun.dividen.show', $run) }}" class="btn btn-ghost btn-sm">Lihat</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    <div>Tiada pengiraan dividen lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $runs->links() }}</div>
@endsection
