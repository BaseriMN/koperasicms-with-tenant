@extends('layouts.master')
@section('title', 'Permohonan Pinjaman')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div><h1>Permohonan Pinjaman</h1><p class="lead">Mohon pinjaman baharu atau semak permohonan sedia ada.</p></div>
    @if (auth()->user()->hasAnyRole(['kerani', 'pengurus', 'admin', 'super-user']))
        <a href="{{ route('pinjaman.create') }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Mohon Pinjaman
        </a>
    @endif
</div>

<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat"><div class="k">Menunggu</div><div class="v">{{ $stats['pending'] }}</div><div class="meta" style="color:var(--gold);">perlu kelulusan</div></div>
    <div class="stat"><div class="k">Diluluskan (bulan ini)</div><div class="v">{{ $stats['approved'] }}</div><div class="meta">▲ aktif</div></div>
    <div class="stat"><div class="k">Jumlah Menunggu</div><div class="v" style="font-size:26px;">RM {{ number_format($stats['requested'], 0) }}</div></div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Senarai Permohonan</h3>
        @if ($canApprove)<span class="badge teal">Mod Kelulusan</span>@endif
    </div>
    <table>
        <thead><tr><th>Pemohon</th><th>Jumlah</th><th>Tempoh</th><th>Status</th><th style="text-align:right;">Tindakan</th></tr></thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td>
                        <div class="cell-main">
                            <div class="av">{{ strtoupper(substr($loan->member->nama ?? '?', 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:600;">
                                    <span class="badge gold" style="font-family:'Fraunces',serif;margin-right:6px;">{{ $loan->member->no_ahli ?? '—' }}</span>
                                    {{ $loan->member->nama ?? '—' }}
                                </div>
                                <div class="cell-sub">{{ Str::limit($loan->tujuan, 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:600;">RM {{ number_format($loan->amount, 2) }}</td>
                    <td>{{ $loan->tempoh }} bln</td>
                    <td>
                        @if ($loan->status === 'pending')<span class="badge gold"><span class="dot"></span>Menunggu</span>
                        @elseif ($loan->status === 'approved')<span class="badge ok"><span class="dot"></span>Diluluskan</span>
                        @else<span class="badge off"><span class="dot"></span>Ditolak</span>@endif
                    </td>
                    <td style="text-align:right;">
                        @if ($canApprove && $loan->status === 'pending')
                            @if ($loan->dimohon_oleh === auth()->id())
                                <span class="cell-sub" title="Anda merekod permohonan ini">Perlu pegawai lain</span>
                            @else
                            <div style="display:inline-flex;gap:8px;" x-data="{ buka: false }">
                                <button class="btn btn-gold btn-sm" type="button" @click="buka = true">Lulus</button>
                                <form method="POST" action="{{ route('pinjaman.decide', $loan) }}" data-confirm="Tolak permohonan ini?">
                                    @csrf <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-danger btn-sm" type="submit">Tolak</button>
                                </form>

                                <div x-show="buka" x-cloak @click.self="buka = false"
                                     style="position:fixed;inset:0;background:rgba(12,31,28,.5);z-index:100;display:grid;place-items:center;padding:20px;">
                                    <div class="panel" style="max-width:460px;width:100%;text-align:left;" @click.stop>
                                        <div class="panel-head"><h3>Kelulusan Pinjaman #{{ $loan->id }}</h3></div>
                                        <form method="POST" action="{{ route('pinjaman.decide', $loan) }}">
                                            @csrf <input type="hidden" name="status" value="approved">
                                            <div class="panel-body">
                                                <div class="field">
                                                    <label>Mesyuarat Kelulusan</label>
                                                    <select class="select" name="meeting_id" required>
                                                        <option value="">— Pilih mesyuarat —</option>
                                                        @foreach ($meetings as $mt)
                                                            <option value="{{ $mt->id }}">{{ $mt->tajuk }} ({{ \Illuminate\Support\Carbon::parse($mt->tarikh)->format('d/m/Y') }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label>Pencadang</label>
                                                    <select class="select" name="pencadang_id" required>
                                                        <option value="">— Pilih ahli —</option>
                                                        @foreach ($ahliList as $a)
                                                            <option value="{{ $a->id }}">{{ $a->no_ahli }} — {{ $a->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label>Penyokong</label>
                                                    <select class="select" name="penyokong_id" required>
                                                        <option value="">— Pilih ahli —</option>
                                                        @foreach ($ahliList as $a)
                                                            <option value="{{ $a->id }}">{{ $a->no_ahli }} — {{ $a->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="field" style="margin-bottom:0;">
                                                    <label>Catatan <span class="hint">(pilihan)</span></label>
                                                    <textarea class="textarea" name="catatan"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-actions" style="padding:16px 22px;">
                                                <button class="btn btn-gold" type="submit">Sahkan Lulus</button>
                                                <button class="btn btn-ghost" type="button" @click="buka = false">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @else
                            <span class="cell-sub">{{ $loan->reviewer->name ?? '—' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
                    <div>Tiada permohonan pinjaman lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:18px;">{{ $loans->links() }}</div>
@endsection
