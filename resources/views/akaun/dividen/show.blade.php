@extends('layouts.master')
@section('title', 'Dividen ' . $dividen->tahun)
@section('crumb', 'Dividen')

@section('content')
@php $draf = $dividen->isDraf(); @endphp

<div class="page-head">
    <div>
        <div style="display:flex;align-items:center;gap:12px;">
            <h1>Dividen {{ $dividen->tahun }}</h1>
            @if ($dividen->isMuktamad())
                <span class="badge ok"><span class="dot"></span>Dimuktamadkan</span>
            @else
                <span class="badge gold"><span class="dot"></span>Draf</span>
            @endif
        </div>
        <p class="lead">Tahun Kewangan {{ optional($dividen->tarikh_mula)->translatedFormat('d M Y') ?? '—' }} – {{ $dividen->tarikh_cutoff->translatedFormat('d M Y') }} · {{ $shares->total() }} ahli</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('akaun.dividen.index') }}" class="btn btn-ghost">Kembali</a>
        @if ($draf)
            <form method="POST" action="{{ route('akaun.dividen.muktamad', $dividen) }}"
                  data-confirm="Muktamadkan dividen {{ $dividen->tahun }}? Tindakan ini akan merekod perbelanjaan & masuk ke saham ahli. TAK BOLEH DIBATALKAN.">
                @csrf
                <button class="btn btn-gold" type="submit">Muktamadkan</button>
            </form>
        @endif
    </div>
</div>

{{-- Ringkasan kewangan --}}
@php
    $lebihSiling = $dividen->jumlah_dividen > $dividen->untung_boleh_agih;
@endphp

<div class="grid grid-3" style="margin-bottom:14px;">
    <div class="stat"><div class="k">Untung Bersih</div><div class="v" style="font-size:24px;">RM {{ number_format($dividen->untung_bersih, 2) }}</div><div class="meta">asas tabung</div></div>
    <div class="stat"><div class="k">Untung Boleh Agih</div><div class="v" style="font-size:24px;">RM {{ number_format($dividen->untung_boleh_agih, 2) }}</div><div class="meta">selepas tolak tabung</div></div>
    <div class="stat"><div class="k">Jumlah Saham Anggota</div><div class="v" style="font-size:24px;">RM {{ number_format($dividen->jumlah_saham_anggota, 2) }}</div><div class="meta">asas dividen</div></div>
</div>

<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat" style="border-color:var(--gold-soft);"><div class="k">Jumlah Dividen ({{ rtrim(rtrim(number_format($dividen->peratus_diluluskan,2),'0'),'.') }}%)</div><div class="v" style="font-size:24px;color:var(--gold);">RM {{ number_format($dividen->jumlah_dividen, 2) }}</div><div class="meta">saham × kadar diluluskan</div></div>
    <div class="stat"><div class="k">Baki Dibawa Ke Hadapan</div><div class="v" style="font-size:24px;">RM {{ number_format($dividen->baki_dibawa_hadapan, 2) }}</div><div class="meta">untung boleh agih − dividen</div></div>
    <div class="stat"><div class="k">Kadar Auditor / Diluluskan</div><div class="v" style="font-size:20px;">{{ rtrim(rtrim(number_format($dividen->peratus_auditor,2),'0'),'.') }}% / {{ rtrim(rtrim(number_format($dividen->peratus_diluluskan,2),'0'),'.') }}%</div></div>
</div>

@if ($lebihSiling)
<div class="alert" style="background:rgba(192,60,40,.08);border-color:rgba(192,60,40,.25);color:#9a3322;margin-bottom:24px;">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
    <span><strong>Amaran:</strong> Jumlah dividen (RM {{ number_format($dividen->jumlah_dividen, 2) }}) melebihi untung boleh agih (RM {{ number_format($dividen->untung_boleh_agih, 2) }}). Sila semak — keputusan di tangan pengurusan.</span>
</div>
@endif

<div class="grid grid-2" style="margin-bottom:24px;align-items:start;">
    {{-- Tabung peruntukan --}}
    <div class="panel">
        <div class="panel-head"><h3>Peruntukan Tabung</h3><span class="badge off">− RM {{ number_format($dividen->jumlah_peruntukan, 2) }}</span></div>
        <table>
            <thead><tr><th>Tabung</th><th>Asas</th><th style="text-align:right;">Amaun</th>@if($draf)<th></th>@endif</tr></thead>
            <tbody>
                @foreach ($dividen->allocations as $a)
                    <tr>
                        <td>{{ $a->nama_tabung }}</td>
                        <td class="cell-sub">{{ $a->jenis_kira === 'peratus' ? rtrim(rtrim(number_format($a->nilai,2),'0'),'.') . '%' : 'Tetap' }}</td>
                        <td style="text-align:right;font-weight:600;">RM {{ number_format($a->amaun, 2) }}</td>
                        @if ($draf)
                        <td style="text-align:right;">
                            <form method="POST" action="{{ route('akaun.dividen.tabung.buang', [$dividen, $a]) }}" data-confirm="Buang tabung ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">×</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($draf)
        {{-- Tambah tabung baharu --}}
        <div class="panel-body" style="border-top:1px solid var(--line);">
            <form method="POST" action="{{ route('akaun.dividen.tabung.tambah', $dividen) }}">
                @csrf
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                    <div class="field" style="flex:2;min-width:140px;margin:0;">
                        <label>Nama Tabung</label>
                        <input class="input" name="nama_tabung" required placeholder="cth: Tabung Khairat">
                    </div>
                    <div class="field" style="flex:1;min-width:110px;margin:0;">
                        <label>Jenis</label>
                        <select class="select" name="jenis_kira">
                            <option value="peratus">Peratus (%)</option>
                            <option value="amaun">Amaun (RM)</option>
                        </select>
                    </div>
                    <div class="field" style="flex:1;min-width:90px;margin:0;">
                        <label>Nilai</label>
                        <input class="input" type="number" name="nilai" step="0.01" min="0" required>
                    </div>
                    <button class="btn btn-ghost" type="submit">Tambah</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- Maklumat run --}}
    <div class="panel">
        <div class="panel-head"><h3>Maklumat Pengiraan</h3></div>
        <div class="panel-body">
            @if ($draf)
                <form method="POST" action="{{ route('akaun.dividen.update', $dividen) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-2">
                        <div class="field">
                            <label>Tarikh Mula</label>
                            <input class="input" type="date" name="tarikh_mula" value="{{ optional($dividen->tarikh_mula)->toDateString() }}" required>
                        </div>
                        <div class="field">
                            <label>Tarikh Cut-off</label>
                            <input class="input" type="date" name="tarikh_cutoff" value="{{ $dividen->tarikh_cutoff->toDateString() }}" required>
                        </div>
                        <div class="field">
                            <label>Untung Bersih (RM)</label>
                            <input class="input" type="number" name="untung_bersih" step="0.01" min="0" value="{{ $dividen->untung_bersih }}" required>
                        </div>
                        <div class="field">
                            <label>Jumlah Saham Anggota (RM)</label>
                            <input class="input" type="number" name="jumlah_saham_anggota" step="0.01" min="0" value="{{ $dividen->jumlah_saham_anggota }}" required>
                            <div class="hint">Boleh ubah ikut audit.</div>
                        </div>
                        <div class="field">
                            <label>Kadar Auditor (%)</label>
                            <input class="input" type="number" name="peratus_auditor" step="0.01" min="0" max="100" value="{{ $dividen->peratus_auditor }}" required>
                        </div>
                        <div class="field">
                            <label>Kadar Diluluskan (%)</label>
                            <input class="input" type="number" name="peratus_diluluskan" step="0.01" min="0" max="100" value="{{ $dividen->peratus_diluluskan }}" required>
                            <div class="hint">Kadar ini dipakai untuk kira.</div>
                        </div>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Catatan</label>
                        <textarea class="textarea" name="catatan">{{ $dividen->catatan }}</textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-gold" type="submit">Kemaskini & Kira Semula</button>
                    </div>
                </form>
            @else
                <div class="field"><label>Tahun Kewangan</label><div>{{ optional($dividen->tarikh_mula)->translatedFormat('d M Y') ?? '—' }} – {{ $dividen->tarikh_cutoff->translatedFormat('d M Y') }}</div></div>
                <div class="field"><label>Jumlah Saham Anggota</label><div>RM {{ number_format($dividen->jumlah_saham_anggota, 2) }}</div></div>
                <div class="field"><label>Kadar Auditor / Diluluskan</label><div>{{ rtrim(rtrim(number_format($dividen->peratus_auditor,2),'0'),'.') }}% / {{ rtrim(rtrim(number_format($dividen->peratus_diluluskan,2),'0'),'.') }}%</div></div>
                <div class="field"><label>Dimuktamadkan</label><div>{{ optional($dividen->tarikh_muktamad)->translatedFormat('d M Y') }}</div></div>
                <div class="field"><label>Dikira Oleh</label><div>{{ $dividen->pengira->name ?? '—' }}</div></div>
                <div class="field" style="margin-bottom:0;"><label>Catatan</label><div>{{ $dividen->catatan ?? '—' }}</div></div>
            @endif
        </div>
    </div>
</div>

{{-- Jadual bahagian ahli --}}
<div class="panel">
    <div class="panel-head"><h3>Bahagian Dividen Ahli</h3><span class="badge">{{ $shares->total() }} ahli</span></div>
    <table>
        <thead>
            <tr><th>No. Ahli</th><th>Nama</th><th style="text-align:right;">Saham Layak</th><th style="text-align:right;">%</th><th style="text-align:right;">Dividen</th><th style="text-align:right;">Tindakan</th></tr>
        </thead>
        <tbody>
            @forelse ($shares as $sh)
                <tr>
                    <td><span class="badge gold" style="font-family:'Fraunces',serif;">{{ $sh->member->no_ahli ?? '—' }}</span></td>
                    <td>
                        {{ $sh->member->nama ?? '—' }}
                        @if ($sh->override)<span class="badge teal" style="margin-left:6px;">override</span>@endif
                    </td>
                    <td style="text-align:right;">
                        @if ($draf)
                            <form method="POST" action="{{ route('akaun.dividen.bahagian.override', [$dividen, $sh]) }}" style="display:inline-flex;gap:4px;align-items:center;justify-content:flex-end;">
                                @csrf @method('PUT')
                                <input class="input" type="number" name="saham_layak" value="{{ rtrim(rtrim(number_format($sh->saham_layak,2,'.',''),'0'),'.') }}" step="0.01" min="0" style="width:110px;padding:6px 10px;text-align:right;">
                                <button class="btn btn-ghost btn-sm" type="submit">✓</button>
                            </form>
                        @else
                            RM {{ number_format($sh->saham_layak, 2) }}
                        @endif
                    </td>
                    <td style="text-align:right;" class="cell-sub">{{ rtrim(rtrim(number_format($sh->peratus,4),'0'),'.') }}%</td>
                    <td style="text-align:right;font-weight:600;">RM {{ number_format($sh->amaun_dividen, 2) }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('akaun.dividen.penyata', [$dividen, $sh]) }}" class="btn btn-ghost btn-sm" target="_blank">Penyata</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">Tiada ahli.</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $shares->links() }}</div>
@endsection
