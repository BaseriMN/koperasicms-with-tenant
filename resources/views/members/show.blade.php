@extends('layouts.master')
@section('title', 'Profil Ahli')
@section('crumb', 'Keahlian')

@section('content')
<div class="page-head">
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,var(--teal),var(--teal-deep));display:grid;place-items:center;color:#fff;font-size:28px;font-weight:600;">
            @if ($member->foto_path)
                <img src="{{ asset('storage/' . $member->foto_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
            @else
                {{ strtoupper(substr($member->nama, 0, 1)) }}
            @endif
        </div>
        <div>
            <div style="display:flex;align-items:center;gap:12px;">
                <h1>{{ $member->nama }}</h1>
                <span class="badge gold" style="font-family:'Fraunces',serif;font-size:14px;">{{ $member->no_ahli }}</span>
            </div>
            <p class="lead">
                @if ($member->status === 'aktif')<span class="badge ok"><span class="dot"></span>Aktif</span>
                @elseif ($member->status === 'tidak_aktif')<span class="badge gold"><span class="dot"></span>Tidak Aktif</span>
                @else<span class="badge off"><span class="dot"></span>Berhenti</span>@endif
                &nbsp; Sertai {{ optional($member->tarikh_sertai)->translatedFormat('d M Y') ?? '—' }}
            </p>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('members.edit', $member) }}" class="btn btn-ghost">Sunting</a>
        <a href="{{ route('member.pindah.form', $member) }}" class="btn btn-ghost">Pindah Milik</a>
        <a href="{{ route('transaksi.create') }}?member={{ $member->id }}" class="btn btn-gold">Rekod Transaksi</a>
    </div>
</div>

{{-- Ringkasan baki --}}
<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="stat"><div class="k">Baki Saham</div><div class="v" style="font-size:30px;">RM {{ number_format($summary['saham'], 2) }}</div></div>
    @if (simpanan_aktif())
    <div class="stat"><div class="k">Baki Simpanan</div><div class="v" style="font-size:30px;">RM {{ number_format($summary['simpanan'], 2) }}</div></div>
    @endif
</div>

<div class="grid grid-2" style="margin-bottom:24px;">
    {{-- Maklumat peribadi --}}
    <div class="panel">
        <div class="panel-head"><h3>Maklumat Peribadi</h3></div>
        <div class="panel-body">
            <div class="field"><label>No. Kad Pengenalan</label><div>{{ $member->no_kp ?? '—' }}</div></div>
            <div class="field"><label>Telefon</label><div>{{ $member->telefon ?? '—' }}</div></div>
            <div class="field"><label>Akaun Login</label><div>{{ $member->user->name ?? '— Tiada —' }}</div></div>
            <div class="field" style="margin-bottom:0;"><label>Alamat</label><div>{{ $member->alamat ?? '—' }}</div></div>
        </div>
    </div>

    {{-- Waris --}}
    <div class="panel">
        <div class="panel-head"><h3>Waris Utama</h3>@if($member->nextOfKin)<span class="badge teal">{{ $member->nextOfKin->hubungan }}</span>@endif</div>
        <div class="panel-body">
            @if ($member->nextOfKin)
                <div class="field"><label>Nama</label><div>{{ $member->nextOfKin->nama }}</div></div>
                <div class="field"><label>No. KP</label><div>{{ $member->nextOfKin->no_kp ?? '—' }}</div></div>
                <div class="field"><label>Telefon</label><div>{{ $member->nextOfKin->telefon ?? '—' }}</div></div>
                <div class="field" style="margin-bottom:0;"><label>Alamat</label><div>{{ $member->nextOfKin->alamat ?? '—' }}</div></div>
            @else
                <div class="empty" style="padding:30px 10px;">
                    <div>Tiada waris direkodkan.</div>
                    <a href="{{ route('members.edit', $member) }}" class="btn btn-ghost btn-sm" style="margin-top:12px;">Tambah Waris</a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Lejar transaksi terkini --}}
<div class="panel" style="margin-bottom:24px;">
    <div class="panel-head">
        <h3>Transaksi Terkini</h3>
        <a href="{{ route('transaksi.index') }}?member_id={{ $member->id }}" class="tool-link" style="color:var(--teal);font-size:13px;">Lihat semua</a>
    </div>
    <table>
        <thead><tr><th>Tarikh</th><th>Jenis</th><th>Arah</th><th>Amaun</th><th>Baki</th><th>Keterangan</th></tr></thead>
        <tbody>
            @forelse ($recent as $t)
                <tr>
                    <td class="cell-sub">{{ $t->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        @if($t->jenis==='saham')
                            <span class="badge gold">Saham</span>
                        @elseif(simpanan_aktif())
                            <span class="badge teal">Simpanan</span>
                        @else
                            <span class="badge teal">{{ ucfirst($t->jenis) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($t->arah==='masuk')<span class="badge ok">Masuk</span>
                        @else<span class="badge off">Keluar</span>@endif
                    </td>
                    <td style="font-weight:600;">RM {{ number_format($t->amaun, 2) }}</td>
                    <td class="cell-sub">RM {{ number_format($t->baki, 2) }}</td>
                    <td class="cell-sub">{{ $t->keterangan ?? $t->sumber }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty" style="padding:36px 10px;">Tiada transaksi lagi.</div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Sejarah pindah milik keahlian --}}
@if ($member->ownershipTransfers->count())
<div class="panel">
    <div class="panel-head"><h3>Sejarah Pindah Milik</h3></div>
    <table>
        <thead><tr><th>Tarikh</th><th>Dari</th><th>Kepada</th><th>Sebab</th></tr></thead>
        <tbody>
            @foreach ($member->ownershipTransfers as $ot)
                <tr>
                    <td class="cell-sub">{{ optional($ot->tarikh_pindah)->translatedFormat('d M Y') }}</td>
                    <td>{{ $ot->from_nama ?? '—' }}</td>
                    <td style="font-weight:600;">{{ $ot->to_nama }}</td>
                    <td class="cell-sub">{{ $ot->sebab ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
