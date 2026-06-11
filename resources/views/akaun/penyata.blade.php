@extends('layouts.master')
@section('title', 'Penyata Untung Rugi')
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Penyata Untung Rugi</h1><p class="lead">Ringkasan pendapatan, perbelanjaan & lebihan koperasi.</p></div>
    <button class="btn btn-ghost" onclick="window.print()">
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-4a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
        Cetak
    </button>
</div>

{{-- Penapis tempoh --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-body" style="padding:16px 22px;">
        <form method="GET" action="{{ route('akaun.penyata') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;"><label>Dari</label><input class="input" type="date" name="dari" value="{{ $dari }}"></div>
            <div class="field" style="margin:0;"><label>Hingga</label><input class="input" type="date" name="hingga" value="{{ $hingga }}"></div>
            <button class="btn btn-gold" type="submit">Jana</button>
        </form>
    </div>
</div>

{{-- Ringkasan utama --}}
<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat"><div class="k">Jumlah Pendapatan</div><div class="v" style="font-size:26px;color:var(--ok);">RM {{ number_format($jumlahPendapatan, 2) }}</div></div>
    <div class="stat"><div class="k">Jumlah Perbelanjaan</div><div class="v" style="font-size:26px;color:var(--danger);">RM {{ number_format($jumlahPerbelanjaan, 2) }}</div></div>
    <div class="stat" style="{{ $lebihan >= 0 ? 'border-color:var(--ok);' : 'border-color:var(--danger);' }}">
        <div class="k">{{ $lebihan >= 0 ? 'Lebihan' : 'Kurangan' }}</div>
        <div class="v" style="font-size:26px;color:{{ $lebihan >= 0 ? 'var(--ok)' : 'var(--danger)' }};">RM {{ number_format(abs($lebihan), 2) }}</div>
        <div class="meta">{{ $lebihan >= 0 ? 'Koperasi untung' : 'Koperasi rugi' }} bagi tempoh ini</div>
    </div>
</div>

<div class="grid grid-2" style="align-items:start;">
    {{-- Pendapatan --}}
    <div class="panel">
        <div class="panel-head" style="background:rgba(47,125,84,.06);"><h3>Pendapatan</h3><span class="badge ok">RM {{ number_format($jumlahPendapatan, 2) }}</span></div>
        <table>
            <tbody>
                @forelse ($pendapatan as $row)
                    <tr>
                        <td>{{ $row['nama'] }}</td>
                        <td style="text-align:right;font-weight:600;">RM {{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td><div class="empty" style="padding:30px 10px;">Tiada pendapatan.</div></td></tr>
                @endforelse
            </tbody>
            @if ($pendapatan->count())
            <tfoot>
                <tr style="border-top:2px solid var(--line);">
                    <td style="padding-left:22px;font-weight:700;font-family:'Fraunces',serif;">Jumlah</td>
                    <td style="text-align:right;padding-right:22px;font-weight:700;font-family:'Fraunces',serif;">RM {{ number_format($jumlahPendapatan, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Perbelanjaan --}}
    <div class="panel">
        <div class="panel-head" style="background:rgba(177,64,47,.05);"><h3>Perbelanjaan</h3><span class="badge off">RM {{ number_format($jumlahPerbelanjaan, 2) }}</span></div>
        <table>
            <tbody>
                @forelse ($perbelanjaan as $row)
                    <tr>
                        <td>{{ $row['nama'] }}</td>
                        <td style="text-align:right;font-weight:600;">RM {{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td><div class="empty" style="padding:30px 10px;">Tiada perbelanjaan.</div></td></tr>
                @endforelse
            </tbody>
            @if ($perbelanjaan->count())
            <tfoot>
                <tr style="border-top:2px solid var(--line);">
                    <td style="padding-left:22px;font-weight:700;font-family:'Fraunces',serif;">Jumlah</td>
                    <td style="text-align:right;padding-right:22px;font-weight:700;font-family:'Fraunces',serif;">RM {{ number_format($jumlahPerbelanjaan, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Baris lebihan/kurangan --}}
<div class="panel" style="margin-top:22px;border-color:{{ $lebihan >= 0 ? 'var(--ok)' : 'var(--danger)' }};">
    <div class="panel-body" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h3 style="font-family:'Fraunces',serif;font-size:18px;">{{ $lebihan >= 0 ? 'Lebihan Bersih' : 'Kurangan Bersih' }}</h3>
            <p class="cell-sub" style="margin-top:4px;">Pendapatan − Perbelanjaan</p>
        </div>
        <div style="font-family:'Fraunces',serif;font-size:30px;color:{{ $lebihan >= 0 ? 'var(--ok)' : 'var(--danger)' }};">
            RM {{ number_format($lebihan, 2) }}
        </div>
    </div>
</div>
@endsection
