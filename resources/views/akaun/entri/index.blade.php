@extends('layouts.master')
@section('title', ucfirst($jenis))
@section('crumb', 'Akaun')

@section('content')
@php
    $isPendapatan = $jenis === 'pendapatan';
    $accent = $isPendapatan ? 'ok' : 'off';
@endphp

<div class="page-head">
    <div>
        <h1>{{ ucfirst($jenis) }}</h1>
        <p class="lead">{{ $isPendapatan ? 'Wang yang diterima koperasi.' : 'Wang yang dibelanjakan koperasi.' }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('akaun.penyata') }}" class="btn btn-ghost">Penyata Untung Rugi</a>
        <a href="{{ route('akaun.kategori.index', $jenis) }}" class="btn btn-ghost">Urus Kategori</a>
        <a href="{{ route('akaun.entri.export.csv', array_merge(['jenis' => $jenis], request()->only('dari', 'hingga', 'category_id'))) }}" class="btn btn-ghost">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12M8 11l4 4 4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            Export CSV
        </a>
        <a href="{{ route('akaun.entri.create', $jenis) }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Rekod {{ ucfirst($jenis) }}
        </a>
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat">
        <div class="k">Jumlah {{ ucfirst($jenis) }}</div>
        <div class="v" style="font-size:26px;">RM {{ number_format($jumlahKeseluruhan, 2) }}</div>
        <div class="meta" style="color:{{ $isPendapatan ? 'var(--ok)' : 'var(--danger)' }};">
            {{ $dari ? \Illuminate\Support\Carbon::parse($dari)->format('d/m/y') : 'semua' }}
            – {{ $hingga ? \Illuminate\Support\Carbon::parse($hingga)->format('d/m/y') : 'kini' }}
        </div>
    </div>
    <div class="stat"><div class="k">Bilangan Rekod</div><div class="v">{{ $entries->total() }}</div></div>
    <div class="stat"><div class="k">Kategori Aktif</div><div class="v">{{ $categories->count() }}</div></div>
</div>

<div class="grid grid-2" style="margin-bottom:24px;align-items:start;">
    {{-- Pecahan ikut kategori --}}
    <div class="panel">
        <div class="panel-head"><h3>Pecahan Mengikut Kategori</h3></div>
        <div class="panel-body" style="padding:8px 0;">
            @forelse ($ringkasan->sortByDesc('total') as $row)
                @php $pct = $jumlahKeseluruhan > 0 ? ($row->total / $jumlahKeseluruhan) * 100 : 0; @endphp
                <div style="padding:11px 22px;">
                    <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;">
                        <span>{{ optional($row->category)->namaPenuh() ?? '—' }}</span>
                        <span style="font-weight:600;">RM {{ number_format($row->total, 2) }}</span>
                    </div>
                    <div style="height:6px;background:var(--bg-2);border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,var(--gold),var(--gold-soft));border-radius:99px;"></div>
                    </div>
                </div>
            @empty
                <div class="empty" style="padding:36px 10px;">Tiada data untuk tempoh ini.</div>
            @endforelse
        </div>
    </div>

    {{-- Penapis --}}
    <div class="panel">
        <div class="panel-head"><h3>Penapis</h3></div>
        <div class="panel-body">
            <form method="GET" action="{{ route('akaun.entri.index', $jenis) }}">
                <div class="grid grid-2">
                    <div class="field"><label>Dari Tarikh</label><input class="input" type="date" name="dari" value="{{ $dari }}"></div>
                    <div class="field"><label>Hingga Tarikh</label><input class="input" type="date" name="hingga" value="{{ $hingga }}"></div>
                </div>
                <div class="field">
                    <label>Kategori</label>
                    <select class="select" name="category_id">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" {{ (string) request('category_id') === (string) $c->id ? 'selected' : '' }}>
                                {{ $c->parent_id ? '— ' : '' }}{{ $c->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:10px;">
                    <button class="btn btn-gold" type="submit">Tapis</button>
                    <a href="{{ route('akaun.entri.index', $jenis) }}" class="btn btn-ghost">Set Semula</a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Senarai entri --}}
<div class="panel">
    <div class="panel-head"><h3>Senarai {{ ucfirst($jenis) }}</h3><span class="badge">{{ $entries->total() }} rekod</span></div>
    <table>
        <thead>
            <tr><th>Tarikh</th><th>Kategori</th><th>Ahli / Pihak</th><th>Rujukan</th><th style="text-align:right;">Amaun</th><th style="text-align:right;">Tindakan</th></tr>
        </thead>
        <tbody>
            @forelse ($entries as $e)
                <tr>
                    <td class="cell-sub">{{ $e->tarikh->translatedFormat('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $accent }}">{{ optional($e->category)->nama ?? '—' }}</span>
                        @if ($e->category && $e->category->parent)
                            <div class="cell-sub" style="margin-top:3px;">{{ $e->category->parent->nama }}</div>
                        @endif
                    </td>
                    <td>
                        @if ($e->member)
                            <a href="{{ route('members.show', $e->member) }}" style="text-decoration:none;">
                                <span class="badge gold" style="font-family:'Fraunces',serif;">{{ $e->member->no_ahli }}</span>
                            </a>
                            <div class="cell-sub" style="margin-top:3px;">{{ $e->member->nama }}</div>
                        @elseif ($e->penerima_pembayar)
                            {{ $e->penerima_pembayar }}
                        @else
                            <span class="cell-sub">—</span>
                        @endif
                    </td>
                    <td class="cell-sub">{{ $e->rujukan ?? '—' }}</td>
                    <td style="text-align:right;font-weight:600;">RM {{ number_format($e->amaun, 2) }}</td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;">
                            <a href="{{ route('akaun.entri.edit', [$jenis, $e]) }}" class="btn btn-ghost btn-sm">Sunting</a>
                            <form method="POST" action="{{ route('akaun.entri.destroy', [$jenis, $e]) }}" data-confirm="Padam rekod ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Padam</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <div>Tiada rekod {{ $jenis }} lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $entries->links() }}</div>
@endsection
