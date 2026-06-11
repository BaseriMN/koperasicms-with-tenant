@extends('layouts.master')
@section('title', 'Lejar Transaksi')
@section('crumb', 'Simpanan & Saham')

@section('content')
<div class="page-head">
    <div><h1>Lejar Transaksi</h1><p class="lead">Rekod penuh keluar-masuk {{ simpanan_aktif() ? 'saham & simpanan' : 'saham' }} ahli.</p></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('transaksi.export.csv', request()->only('member_id','jenis','dari','hingga')) }}" class="btn btn-ghost">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12M8 11l4 4 4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            Export CSV
        </a>
        <a href="{{ route('saham.pindah.form') }}" class="btn btn-ghost">Pindah Saham</a>
        <a href="{{ route('transaksi.create') }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Rekod Transaksi
        </a>
    </div>
</div>

@php
    $hariIni = now();
    $cepat = [
        'Bulan Ini'  => [$hariIni->copy()->startOfMonth()->toDateString(), $hariIni->copy()->endOfMonth()->toDateString()],
        'Bulan Lalu' => [$hariIni->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $hariIni->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        'Tahun Ini'  => [$hariIni->copy()->startOfYear()->toDateString(), $hariIni->copy()->endOfYear()->toDateString()],
        'Tahun Lalu' => [$hariIni->copy()->subYear()->startOfYear()->toDateString(), $hariIni->copy()->subYear()->endOfYear()->toDateString()],
    ];
@endphp

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-body" style="padding:16px 22px;">
        {{-- Pilihan cepat --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
            @foreach ($cepat as $label => $julat)
                @php $aktif = request('dari') === $julat[0] && request('hingga') === $julat[1]; @endphp
                <a href="{{ route('transaksi.index', array_merge(request()->only('member_id','jenis'), ['dari'=>$julat[0],'hingga'=>$julat[1]])) }}"
                   class="btn btn-sm {{ $aktif ? 'btn-gold' : 'btn-ghost' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('transaksi.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div class="field" style="flex:1;min-width:180px;margin:0;">
                <label style="font-size:12px;">Ahli</label>
                <select class="select" name="member_id">
                    <option value="">Semua ahli</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" {{ (string) request('member_id') === (string) $m->id ? 'selected' : '' }}>{{ $m->no_ahli }} — {{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="max-width:150px;margin:0;">
                <label style="font-size:12px;">Jenis</label>
                <select class="select" name="jenis">
                    <option value="">Semua jenis</option>
                    <option value="saham" {{ request('jenis')==='saham' ? 'selected' : '' }}>Saham</option>
                    @if (simpanan_aktif())
                    <option value="simpanan" {{ request('jenis')==='simpanan' ? 'selected' : '' }}>Simpanan</option>
                @endif
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
            @if (request('member_id') || request('jenis') || request('dari') || request('hingga'))
                <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>
</div>

{{-- Kad jumlah dalam tempoh ditapis --}}
<div class="grid grid-3" style="margin-bottom:18px;">
    <div class="stat"><div class="k">Jumlah Masuk</div><div class="v" style="font-size:24px;color:var(--teal);">RM {{ number_format($jumlah['masuk'], 2) }}</div></div>
    <div class="stat"><div class="k">Jumlah Keluar</div><div class="v" style="font-size:24px;color:#9a3322;">RM {{ number_format($jumlah['keluar'], 2) }}</div></div>
    <div class="stat" style="border-color:var(--gold-soft);"><div class="k">Bersih</div><div class="v" style="font-size:24px;color:var(--gold);">RM {{ number_format($jumlah['bersih'], 2) }}</div></div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Transaksi</h3><span class="badge">{{ $transactions->total() }} rekod</span></div>
    <table>
        <thead><tr><th>Tarikh</th><th>Ahli</th><th>Jenis</th><th>Arah</th><th>Amaun</th><th>Baki</th><th>Sumber</th></tr></thead>
        <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td class="cell-sub">{{ $t->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $t->member->no_ahli ?? '—' }}</div>
                        <div class="cell-sub">{{ $t->member->nama ?? '' }}</div>
                    </td>
                    <td>@if($t->jenis==='saham')<span class="badge gold">Saham</span>@else<span class="badge teal">Simpanan</span>@endif</td>
                    <td>@if($t->arah==='masuk')<span class="badge ok">Masuk</span>@else<span class="badge off">Keluar</span>@endif</td>
                    <td style="font-weight:600;">RM {{ number_format($t->amaun, 2) }}</td>
                    <td class="cell-sub">RM {{ number_format($t->baki, 2) }}</td>
                    <td class="cell-sub">{{ $t->sumber }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3v18M7 8h7a3 3 0 010 6H6"/></svg>
                    <div>Tiada transaksi lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $transactions->links() }}</div>
@endsection
