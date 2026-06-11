<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penyata Dividen {{ $dividen->tahun }} — {{ $bahagian->member->no_ahli ?? '' }}</title>
    @php
        use App\Models\Setting;
        $namaKoperasi = Setting::get('nama_koperasi', 'Koperasi');
        $noDaftar     = Setting::get('no_pendaftaran', '');
        $logoPath     = Setting::get('logo_path', '');
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0c1f1c; --gold:#c0962c; --teal-deep:#0f433a; --line:#e1dccf; --muted:#7c8783; --bg:#f4f1ea; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Outfit',sans-serif; color:var(--ink); background:var(--bg); padding:40px 20px; }
        .sheet { max-width:760px; margin:0 auto; background:#fff; border:1px solid var(--line); border-radius:14px; overflow:hidden; box-shadow:0 12px 40px -16px rgba(12,31,28,.25); }
        .head { background:linear-gradient(135deg, var(--teal-deep), var(--ink)); color:#fff; padding:30px 36px; display:flex; align-items:center; gap:18px; }
        .head .logo { width:60px; height:60px; border-radius:14px; background:linear-gradient(135deg,var(--gold),#e3c976); display:grid; place-items:center; font-family:'Fraunces',serif; font-weight:700; font-size:28px; color:var(--ink); overflow:hidden; flex-shrink:0; }
        .head .logo img { width:100%; height:100%; object-fit:contain; background:#fff; }
        .head h1 { font-family:'Fraunces',serif; font-size:22px; }
        .head .sub { font-size:12.5px; color:#aebbb7; margin-top:3px; }
        .body { padding:36px; }
        .title { text-align:center; margin-bottom:30px; }
        .title h2 { font-family:'Fraunces',serif; font-size:24px; }
        .title p { color:var(--muted); font-size:14px; margin-top:4px; }
        .info { display:grid; grid-template-columns:1fr 1fr; gap:14px 30px; margin-bottom:30px; padding:20px 24px; background:var(--bg); border-radius:12px; }
        .info .k { font-size:11px; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); }
        .info .v { font-size:15px; font-weight:600; margin-top:2px; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        td { padding:13px 4px; border-bottom:1px solid var(--line); font-size:14.5px; }
        .amt { text-align:right; font-weight:600; }
        .total { background:var(--ink); color:#fff; }
        .total td { font-family:'Fraunces',serif; font-size:18px; padding:18px 16px; border:0; }
        .foot { padding:24px 36px; border-top:1px solid var(--line); color:var(--muted); font-size:12px; display:flex; justify-content:space-between; }
        .print-btn { display:block; max-width:760px; margin:0 auto 16px; text-align:right; }
        .print-btn button { padding:11px 20px; border:none; border-radius:10px; background:var(--gold); color:var(--ink); font:inherit; font-weight:600; cursor:pointer; }
        @media print { body { background:#fff; padding:0; } .sheet { border:0; box-shadow:none; } .print-btn { display:none; } }
    </style>
</head>
<body>
    <div class="print-btn"><button onclick="window.print()">🖨 Cetak</button></div>

    <div class="sheet">
        <div class="head">
            <div class="logo">
                @if ($logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo">
                @else
                    {{ strtoupper(substr($namaKoperasi, 0, 1)) }}
                @endif
            </div>
            <div>
                <h1>{{ $namaKoperasi }}</h1>
                @if ($noDaftar)<div class="sub">No. Pendaftaran: {{ $noDaftar }}</div>@endif
            </div>
        </div>

        <div class="body">
            <div class="title">
                <h2>Penyata Dividen</h2>
                <p>Bagi Tahun Kewangan {{ $dividen->tahun }}</p>
            </div>

            <div class="info">
                <div><div class="k">No. Ahli</div><div class="v">{{ $bahagian->member->no_ahli ?? '—' }}</div></div>
                <div><div class="k">Nama Ahli</div><div class="v">{{ $bahagian->member->nama ?? '—' }}</div></div>
                <div><div class="k">No. KP</div><div class="v">{{ $bahagian->member->no_kp ?? '—' }}</div></div>
                <div><div class="k">Tahun Kewangan</div><div class="v">{{ optional($dividen->tarikh_mula)->translatedFormat('d/m/Y') ?? '—' }} – {{ $dividen->tarikh_cutoff->translatedFormat('d/m/Y') }}</div></div>
                <div><div class="k">Kadar Dividen Diluluskan</div><div class="v">{{ rtrim(rtrim(number_format($dividen->peratus_diluluskan, 2), '0'), '.') }}%</div></div>
            </div>

            <table>
                <tr>
                    <td>Saham Layak (sehingga cut-off)</td>
                    <td class="amt">RM {{ number_format($bahagian->saham_layak, 2) }}</td>
                </tr>
                <tr>
                    <td>Kadar Dividen Diluluskan</td>
                    <td class="amt">{{ rtrim(rtrim(number_format($dividen->peratus_diluluskan, 2), '0'), '.') }}%</td>
                </tr>
                <tr>
                    <td>Peratus Bahagian (daripada jumlah saham anggota)</td>
                    <td class="amt">{{ rtrim(rtrim(number_format($bahagian->peratus, 4), '0'), '.') }}%</td>
                </tr>
                <tr class="total">
                    <td>Dividen Anda</td>
                    <td class="amt">RM {{ number_format($bahagian->amaun_dividen, 2) }}</td>
                </tr>
            </table>

            @if ($dividen->isMuktamad())
                <p style="margin-top:20px;font-size:13px;color:var(--muted);line-height:1.6;">
                    Dividen ini telah dikreditkan ke akaun saham anda pada {{ optional($dividen->tarikh_muktamad)->translatedFormat('d M Y') }}.
                </p>
            @else
                <div style="margin-top:20px;padding:14px 18px;background:rgba(192,150,44,.10);border:1px solid rgba(192,150,44,.35);border-radius:10px;color:#8a6d1f;font-size:13px;line-height:1.6;">
                    <strong>⚠ DRAF — belum dimuktamadkan.</strong><br>
                    Angka dalam penyata ini bersifat sementara dan belum dikreditkan ke akaun saham. Penyata rasmi akan dikeluarkan selepas dividen dimuktamadkan dalam mesyuarat.
                </div>
            @endif
        </div>

        <div class="foot">
            <span>Dijana pada {{ now()->translatedFormat('d M Y, H:i') }}</span>
            <span>{{ $namaKoperasi }}</span>
        </div>
    </div>
</body>
</html>
