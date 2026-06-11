@extends('layouts.master')
@section('title', 'Imbangan Duga')
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Imbangan Duga</h1><p class="lead">Ringkasan debit & kredit mengikut akaun.</p></div>
    <button class="btn btn-ghost" onclick="window.print()">
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-4a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
        Cetak
    </button>
</div>

{{-- Penapis tempoh --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-body" style="padding:16px 22px;">
        <form method="GET" action="{{ route('akaun.imbangan_duga') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;"><label>Dari</label><input class="input" type="date" name="dari" value="{{ $dari }}"></div>
            <div class="field" style="margin:0;"><label>Hingga</label><input class="input" type="date" name="hingga" value="{{ $hingga }}"></div>
            <button class="btn btn-gold" type="submit">Jana</button>
        </form>
    </div>
</div>

{{-- Ringkasan kad --}}
<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat" style="border-color:var(--ok);">
        <div class="k">Pendapatan (Kredit)</div>
        <div class="v" style="font-size:26px;color:var(--ok);">RM {{ number_format($totalCredit, 2) }}</div>
        <div class="meta">Jumlah kredit</div>
    </div>
    <div class="stat" style="border-color:var(--danger);">
        <div class="k">Perbelanjaan (Debit)</div>
        <div class="v" style="font-size:26px;color:var(--danger);">RM {{ number_format($totalDebit, 2) }}</div>
        <div class="meta">Jumlah debit</div>
    </div>
    <div class="stat" style="{{ $totalDebit == $totalCredit ? 'border-color:var(--ok);' : 'border-color:var(--danger);' }}">
        <div class="k">Imbangan</div>
        <div class="v" style="font-size:26px;color:{{ $totalDebit == $totalCredit ? 'var(--ok)' : 'var(--danger)' }};">
            {{ $totalDebit == $totalCredit ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}
        </div>
    </div>
</div>

{{-- Jadual Format Lajur --}}
<div class="panel">
    <div class="panel-head"><h3>Imbangan Duga</h3><span class="badge">{{ date('d/m/Y', strtotime($dari)) }} - {{ date('d/m/Y', strtotime($hingga)) }}</span></div>
    <div class="panel-body" style="padding:0; overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8f9fc; border-bottom:2px solid var(--line);">
                    <th style="text-align:left; padding:14px 12px;">Kod Akaun</th>
                    <th style="text-align:left; padding:14px 12px;">Nama Akaun</th>
                    <th style="text-align:right; padding:14px 12px; width:150px; background:rgba(47,125,84,.05);">Debit (RM) <span style="font-weight:normal;">▼</span></th>
                    <th style="text-align:right; padding:14px 12px; width:150px; background:rgba(177,64,47,.05);">Kredit (RM) <span style="font-weight:normal;">▼</span></th>
                </tr>
                <tr style="background:#f8f9fc; border-bottom:1px solid var(--line);">
                    <th colspan="2" style="padding:6px 12px; font-weight:normal; font-size:12px;"></th>
                    <th style="text-align:right; padding:6px 12px; font-size:12px; color:var(--ok);">Pendapatan</th>
                    <th style="text-align:right; padding:6px 12px; font-size:12px; color:var(--danger);">Perbelanjaan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $debitTotal = 0;
                    $creditTotal = 0;
                    $debitItems = [];
                    $creditItems = [];
                @endphp
                
                {{-- Asingkan debit dan kredit --}}
                @foreach($items as $item)
                    @if($item['jenis'] == 'pendapatan')
                        @php 
                            $debitItems[] = $item;
                            $debitTotal += $item['jumlah'];
                        @endphp
                    @else
                        @php 
                            $creditItems[] = $item;
                            $creditTotal += $item['jumlah'];
                        @endphp
                    @endif
                @endforeach
                
                {{-- Papar DEBIT dulu (Pendapatan) --}}
                @foreach($debitItems as $item)
                    <tr style="border-bottom:1px solid #e9ecef;">
                        <td style="padding:10px 12px;">{{ $item['kod'] ?? '-' }}</td>
                        <td style="padding:10px 12px;">{{ $item['nama'] }}</td>
                        <td style="text-align:right; padding:10px 12px; color:var(--ok); font-weight:500;">
                            {{ number_format($item['jumlah'], 2) }}
                        </td>
                        <td style="text-align:right; padding:10px 12px;">-</td>
                    </tr>
                @endforeach
                
                {{-- Papar KREDIT lepas tu (pendapatan) --}}
                @foreach($creditItems as $item)
                    <tr style="border-bottom:1px solid #e9ecef;">
                        <td style="padding:10px 12px;">{{ $item['kod'] ?? '-' }}</td>
                        <td style="padding:10px 12px;">{{ $item['nama'] }}</td>
                        <td style="text-align:right; padding:10px 12px;">-</td>
                        <td style="text-align:right; padding:10px 12px; color:var(--danger); font-weight:500;">
                            {{ number_format($item['jumlah'], 2) }}
                        </td>
                    </tr>
                @endforeach
                
                {{-- Baris JUMLAH --}}
                <tr style="border-top:2px solid var(--line); background:#f8f9fc; font-weight:700;">
                    <td colspan="2" style="padding:12px; text-align:right;">JUMLAH</td>
                    <td style="text-align:right; padding:12px; color:var(--ok);">RM {{ number_format($debitTotal, 2) }}</td>
                    <td style="text-align:right; padding:12px; color:var(--danger);">RM {{ number_format($creditTotal, 2) }}</td>
                </tr>
                
                {{-- Baris IMBANGAN --}}
                @if($debitTotal != $creditTotal)
                <tr style="background:rgba(177,64,47,.08); font-weight:700;">
                    <td colspan="3" style="padding:12px; text-align:right;">Perbezaan (Tak Seimbang)</td>
                    <td style="text-align:right; padding:12px; color:var(--danger);">RM {{ number_format(abs($debitTotal - $creditTotal), 2) }}</td>
                </tr>
                @else
                <tr style="background:rgba(47,125,84,.08); font-weight:700;">
                    <td colspan="4" style="padding:12px; text-align:center; color:var(--ok);">✅ IMBANGAN SEIMBANG</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection