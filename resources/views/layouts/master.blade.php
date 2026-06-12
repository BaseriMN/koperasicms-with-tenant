@php
    use App\Models\Setting;

    $palet = Setting::get('tema_palet', config('themes.default'));
    $mode  = Setting::get('tema_mode', 'light');
    $themes = config('themes.palettes');
    // fallback kalau palet tersimpan tak wujud lagi
    $c = $themes[$palet][$mode] ?? $themes[config('themes.default')]['light'];

    $namaKoperasi = Setting::get('nama_koperasi', 'Koperasi');
    $namaPendek   = Setting::get('nama_pendek', 'Koperasi');
    $logoPath     = Setting::get('logo_path', '');
@endphp


<!DOCTYPE html>
<html lang="ms" x-data="{ sidebarOpen: false, dark: false }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $namaKoperasi }}</title>
    @if (!empty($logoPath))
        <link rel="icon" type="image/png" href="{{ tenant_asset($logoPath) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
                    --ink: {{ $c['ink'] }};
                    --ink-2: {{ $c['ink-2'] }};
                    --panel: {{ $c['panel'] }};
                    --bg: {{ $c['bg'] }};
                    --bg-2: {{ $c['bg-2'] }};
                    --gold: {{ $c['gold'] }};
                    --gold-soft: {{ $c['gold-soft'] }};
                    --teal: {{ $c['teal'] }};
                    --teal-deep: {{ $c['teal-deep'] }};
                    --line: {{ $c['line'] }};
                    --muted: {{ $c['muted'] }};
                    --danger: {{ $c['danger'] }};
                    --ok: {{ $c['ok'] }};
                    --shadow: 0 1px 2px rgba(0,0,0,.06), 0 12px 30px -12px rgba(0,0,0,.25);
                    --radius: 14px;
                }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        h1,h2,h3,h4 { font-family: 'Fraunces', serif; font-weight: 600; letter-spacing: -.01em; }

        .app { display: grid; grid-template-columns: 264px 1fr; min-height: 100vh; }

        /* ---------- Sidebar ---------- */
        .sidebar {
            background: linear-gradient(180deg, var(--ink) 0%, var(--ink-2) 100%);
            color: #d9e2df;
            padding: 26px 18px;
            position: sticky; top: 0; height: 100vh;
            display: flex; flex-direction: column;
            border-right: 1px solid rgba(255,255,255,.05);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 99px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(192,150,44,.4); }
        .brand { display: flex; align-items: flex-start; gap: 12px; padding: 0 6px 26px; flex-shrink: 0; }
        .brand .mark {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-soft) 100%);
            display: grid; place-items: center;
            font-family: 'Fraunces', serif; font-weight: 700; color: var(--ink); font-size: 20px;
            box-shadow: 0 6px 18px -6px rgba(192,150,44,.6);
        }
        .brand .name { font-family: 'Fraunces', serif; font-size: 17px; color: #fff; line-height: 1.1; }
        .brand .sub { font-size: 11px; color: var(--gold-soft); letter-spacing: .12em; text-transform: uppercase; }

        .nav-group { margin-top: 4px; }

        /* Tajuk group yang boleh diklik (accordion) */
        .nav-toggle {
            display: flex; align-items: center; justify-content: space-between;
            width: 100%; background: none; border: none; cursor: pointer;
            font-family: 'Outfit', sans-serif;
            font-size: 10px; letter-spacing: .16em; text-transform: uppercase; color: #5e6f6a;
            padding: 16px 12px 8px; transition: color .16s;
        }
        .nav-toggle:hover { color: #91a09b; }
        .nav-toggle .chev { width: 13px; height: 13px; transition: transform .22s ease; opacity: .7; }
        .nav-toggle.open .chev { transform: rotate(180deg); }

        .nav-items { display: flex; flex-direction: column; gap: 2px; overflow: hidden; }

        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px; border-radius: 10px;
            color: #b3c0bc; text-decoration: none; font-size: 14.5px; font-weight: 500;
            transition: all .18s ease; position: relative;
        }
        .nav-link svg { width: 19px; height: 19px; opacity: .8; flex-shrink: 0; }
        .nav-link:hover { background: rgba(255,255,255,.05); color: #fff; }
        .nav-link.active { background: rgba(192,150,44,.14); color: #fff; }
        .nav-link.active::before {
            content: ''; position: absolute; left: -18px; top: 50%; transform: translateY(-50%);
            width: 4px; height: 22px; border-radius: 0 4px 4px 0; background: var(--gold);
        }
        .nav-link.active svg { opacity: 1; color: var(--gold-soft); }

        .side-foot { margin-top: auto; padding-top: 18px; border-top: 1px solid rgba(255,255,255,.07); flex-shrink: 0; }
        .side-user { display: flex; align-items: center; gap: 11px; padding: 8px 6px; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-deep) 100%);
            display: grid; place-items: center; color: #fff; font-weight: 600; font-size: 15px;
        }
        .side-user .u-name { font-size: 13.5px; color: #fff; font-weight: 500; }
        .side-user .u-role { font-size: 11px; color: var(--gold-soft); }

        /* ---------- Main ---------- */
        .main { display: flex; flex-direction: column; min-width: 0; }
        .topbar {
            height: 70px; padding: 0 30px;
            display: flex; align-items: center; justify-content: space-between;
            background: var(--panel); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 30;
        }
        .topbar .title-wrap h2 { font-size: 19px; }
        .topbar .crumb { font-size: 12px; color: var(--muted); letter-spacing: .04em; }
        .top-actions { display: flex; align-items: center; gap: 14px; }
        .icon-btn {
            width: 40px; height: 40px; border-radius: 10px; border: 1px solid var(--line);
            background: var(--panel); cursor: pointer; display: grid; place-items: center; color: var(--ink);
            transition: all .16s ease;
        }
        .icon-btn:hover { border-color: var(--gold); color: var(--gold); }
        .icon-btn svg { width: 18px; height: 18px; }

        .hamburger { display: none; }

        .content { padding: 30px; flex: 1; }
        .content-inner { max-width: 1180px; margin: 0 auto; animation: rise .5s ease both; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 26px; flex-wrap: wrap; }
        .page-head h1 { font-size: 30px; }
        .page-head .lead { color: var(--muted); font-size: 14.5px; margin-top: 4px; }

        /* ---------- Cards / panels ---------- */
        .panel {
            background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
        }
        .panel-head { padding: 18px 22px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
        .panel-head h3 { font-size: 16px; }
        .panel-body { padding: 22px; }

        .grid { display: grid; gap: 20px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }

        .stat {
            background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius);
            padding: 22px; box-shadow: var(--shadow); position: relative; overflow: hidden;
        }
        .stat::after { content:''; position:absolute; right:-30px; top:-30px; width:110px; height:110px; border-radius:50%; background: var(--bg-2); opacity:.5; }
        .stat .k { font-size: 12px; color: var(--muted); letter-spacing: .08em; text-transform: uppercase; }
        .stat .v { font-family: 'Fraunces', serif; font-size: 34px; margin-top: 6px; color: var(--ink); position: relative; }
        .stat .meta { font-size: 12.5px; color: var(--ok); margin-top: 6px; position: relative; }

        /* ---------- Module tiles (dashboard) ---------- */
        .tiles { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
        .tile {
            display: block; text-decoration: none; color: inherit;
            background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius);
            padding: 22px; box-shadow: var(--shadow); transition: transform .2s ease, box-shadow .2s ease, border-color .2s;
        }
        .tile:hover { transform: translateY(-4px); border-color: var(--gold-soft); box-shadow: 0 18px 40px -16px rgba(12,31,28,.3); }
        .tile .ico { width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center; background: linear-gradient(135deg, var(--teal) 0%, var(--teal-deep) 100%); color: #fff; margin-bottom: 14px; }
        .tile .ico svg { width: 22px; height: 22px; }
        .tile h4 { font-size: 16.5px; }
        .tile p { font-size: 13px; color: var(--muted); margin-top: 5px; }

        /* ---------- Table ---------- */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; font-size: 11px; letter-spacing: .08em; text-transform: uppercase;
            color: var(--muted); padding: 13px 22px; border-bottom: 1px solid var(--line); font-weight: 600;
        }
        tbody td { padding: 15px 22px; border-bottom: 1px solid var(--line); font-size: 14px; }
        tbody tr { transition: background .14s; }
        tbody tr:hover { background: var(--bg-2); }
        tbody tr:last-child td { border-bottom: 0; }
        .cell-main { display: flex; align-items: center; gap: 12px; }
        .cell-main .av { width: 34px; height: 34px; border-radius: 9px; background: var(--bg-2); display: grid; place-items: center; font-weight: 600; font-size: 13px; color: var(--teal-deep); }
        .cell-sub { font-size: 12px; color: var(--muted); }

        /* ---------- Badges ---------- */
        .badge {
            display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px;
            border-radius: 999px; font-size: 11.5px; font-weight: 600; letter-spacing: .02em;
            background: var(--bg-2); color: var(--ink-2);
        }
        .badge.gold { background: rgba(192,150,44,.16); color: #8a6a16; }
        .badge.teal { background: rgba(31,111,92,.14); color: var(--teal-deep); }
        .badge.ok { background: rgba(47,125,84,.14); color: var(--ok); }
        .badge.off { background: rgba(177,64,47,.12); color: var(--danger); }
        .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
            padding: 11px 18px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 600;
            border: 1px solid transparent; text-decoration: none; transition: all .16s ease;
        }
        .btn svg { width: 17px; height: 17px; }
        .btn-primary { background: var(--ink); color: #fff; }
        .btn-primary:hover { background: var(--teal-deep); }
        .btn-gold { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-soft) 100%); color: var(--ink); }
        .btn-gold:hover { filter: brightness(1.05); box-shadow: 0 8px 22px -8px rgba(192,150,44,.7); }
        .btn-ghost { background: var(--panel); border-color: var(--line); color: var(--ink); }
        .btn-ghost:hover { border-color: var(--gold); color: var(--gold); }
        .btn-danger { background: transparent; color: var(--danger); border-color: rgba(177,64,47,.3); }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        .btn-sm { padding: 7px 12px; font-size: 13px; }

        /* ---------- Forms ---------- */
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 7px; color: var(--ink-2); }
        .field .hint { font-size: 12px; color: var(--muted); margin-top: 6px; }
        .input, .select, .textarea {
            width: 100%; padding: 12px 14px; border: 1px solid var(--line); border-radius: 10px;
            font: inherit; font-size: 14px; background: var(--panel); color: var(--ink); transition: all .16s;
        }
        .input:focus, .select:focus, .textarea:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(192,150,44,.15); }
        .textarea { min-height: 110px; resize: vertical; }
        .check-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; }
        .check {
            display: flex; align-items: center; gap: 10px; padding: 11px 14px;
            border: 1px solid var(--line); border-radius: 10px; cursor: pointer; transition: all .14s; font-size: 13.5px;
        }
        .check:hover { border-color: var(--gold-soft); background: var(--bg-2); }
        .check input { accent-color: var(--gold); width: 16px; height: 16px; }
        .form-actions { display: flex; gap: 12px; margin-top: 24px; padding-top: 22px; border-top: 1px solid var(--line); }

        .err { color: var(--danger); font-size: 12.5px; margin-top: 6px; }

        /* ---------- Flash / alerts ---------- */
        .alert {
            display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 12px;
            margin-bottom: 22px; font-size: 14px; font-weight: 500; border: 1px solid;
            animation: rise .4s ease both;
        }
        .alert.success { background: rgba(47,125,84,.08); border-color: rgba(47,125,84,.25); color: var(--ok); }
        .alert.error { background: rgba(177,64,47,.07); border-color: rgba(177,64,47,.25); color: var(--danger); }
        .alert svg { width: 20px; height: 20px; flex-shrink: 0; }

        .empty { text-align: center; padding: 56px 20px; color: var(--muted); }
        .empty svg { width: 46px; height: 46px; opacity: .4; margin-bottom: 14px; }

        /* ---------- Responsive ---------- */
        @media (max-width: 920px) {
            .app { grid-template-columns: 1fr; }
            .sidebar {
                position: fixed; z-index: 60; width: 264px; left: 0; top: 0;
                transform: translateX(-110%); transition: transform .28s ease;
            }
            .sidebar.show { transform: translateX(0); box-shadow: 0 0 60px rgba(0,0,0,.4); }
            .hamburger { display: grid; }
            .grid-3, .grid-2, .tiles, .check-grid { grid-template-columns: 1fr; }
            .scrim { position: fixed; inset: 0; background: rgba(12,31,28,.5); z-index: 55; }
        }
        [x-cloak] { display: none !important; }
    </style>
    @stack('head')
</head>
<body>
<div class="app">

    <!-- ===== Sidebar ===== -->
    <aside class="sidebar" :class="{ 'show': sidebarOpen }" id="sidebar">
            <div class="brand">
            @if ($logoPath)
                <div class="mark" style="background:#fff;padding:4px;overflow:hidden;">
                    <img src="{{ tenant_asset($logoPath) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
                </div>
            @else
                <div class="mark">{{ strtoupper(substr($namaPendek, 0, 1)) }}</div>
            @endif
            <div>
                <div class="name">{{ $namaPendek }}</div>
                <div class="sub">{{ \Illuminate\Support\Str::limit($namaKoperasi, 50) }}</div>
            </div>
        </div>

        @php
            $r = fn($p) => request()->routeIs($p) ? 'active' : '';
            $can = fn($key) => auth()->check() && \App\Support\ModuleAccess::userCan(auth()->user(), $key);

            // Tentukan group mana yang patut auto-buka berdasarkan page semasa
            $inPengurusan = request()->routeIs('users.*','members.*','transaksi.*','pinjaman.*','mesyuarat.*','audit.*');
            $inAkaun      = request()->routeIs('akaun.*');
            $inSistem     = request()->routeIs('roles.*','permissions.*','tetapan.modul');
        @endphp

        <!-- Utama -->
        <div class="nav-group">
            <div class="nav-items">
                <a href="{{ route('dashboard') }}" class="nav-link {{ $r('dashboard') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Pengurusan -->
        @if ($can('pengurusan_staff') || $can('pengurusan_member') || $can('lejar_transaksi') || $can('permohonan_pinjaman') || $can('mesyuarat_minit') || $can('laporan_audit'))
        <div class="nav-group" x-data="{ open: {{ $inPengurusan ? 'true' : 'false' }} }">
            <button type="button" class="nav-toggle" :class="{ 'open': open }" @click="open = !open">
                <span>Pengurusan</span>
                <svg class="chev" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="nav-items" x-show="open" x-collapse x-cloak>
                @if ($can('pengurusan_staff'))
                <a href="{{ route('users.index') }}" class="nav-link {{ $r('users.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0111 0M16 6.5a3 3 0 010 6M21 20a4.8 4.8 0 00-4-4.7"/></svg>
                    Pengurusan Staff
                </a>
                @endif
                @if ($can('pengurusan_member'))
                <a href="{{ route('members.index') }}" class="nav-link {{ $r('members.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
                    Keahlian (AXXXX)
                </a>
                @endif
                @if ($can('lejar_transaksi'))
                <a href="{{ route('transaksi.index') }}" class="nav-link {{ $r('transaksi.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                    Lejar Transaksi
                </a>
                @endif
                @if ($can('permohonan_pinjaman') && pinjaman_aktif())
                <a href="{{ route('pinjaman.index') }}" class="nav-link {{ $r('pinjaman.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                    Permohonan Pinjaman
                </a>
                @endif
                @if ($can('mesyuarat_minit'))
                <a href="{{ route('mesyuarat.index') }}" class="nav-link {{ $r('mesyuarat.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                    Mesyuarat &amp; Minit
                </a>
                @endif
                @if ($can('laporan_audit'))
                <a href="{{ route('audit.index') }}" class="nav-link {{ $r('audit.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h12l4 4v12H4z"/><path d="M8 13l2.5 2.5L16 10"/></svg>
                    Laporan Audit
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Akaun -->
        @if ($can('akaun'))
        <div class="nav-group" x-data="{ open: {{ $inAkaun ? 'true' : 'false' }} }">
            <button type="button" class="nav-toggle" :class="{ 'open': open }" @click="open = !open">
                <span>Akaun</span>
                <svg class="chev" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="nav-items" x-show="open" x-collapse x-cloak>
                <a href="{{ route('akaun.entri.index', 'pendapatan') }}" class="nav-link {{ request()->routeIs('akaun.entri.*') && request('jenis')==='pendapatan' ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    Pendapatan
                </a>
                <a href="{{ route('akaun.entri.index', 'perbelanjaan') }}" class="nav-link {{ request()->routeIs('akaun.entri.*') && request('jenis')==='perbelanjaan' ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                    Perbelanjaan
                </a>
                <a href="{{ route('akaun.penyata') }}" class="nav-link {{ $r('akaun.penyata') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 14l3-3 2 2 3-4"/></svg>
                    Penyata Untung Rugi
                </a>
                <a href="{{ route('akaun.imbangan_duga') }}" class="nav-link {{ $r('akaun.imbangan_duga') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 14l3-3 2 2 3-4"/></svg>
                    Imbangan Duga
                </a>
                <a href="{{ route('akaun.dividen.index') }}" class="nav-link {{ $r('akaun.dividen.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    Dividen
                </a>
            </div>
        </div>
        @endif

        <!-- Sistem -->
        @if ($can('tetapan_sistem'))
        <div class="nav-group" x-data="{ open: {{ $inSistem ? 'true' : 'false' }} }">
            <button type="button" class="nav-toggle" :class="{ 'open': open }" @click="open = !open">
                <span>Sistem</span>
                <svg class="chev" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="nav-items" x-show="open" x-collapse x-cloak>
                <a href="{{ route('roles.index') }}" class="nav-link {{ $r('roles.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7z"/></svg>
                    Peranan (Roles)
                </a>
                <a href="{{ route('permissions.index') }}" class="nav-link {{ $r('permissions.*') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>
                    Kebenaran
                </a>
                <a href="{{ route('tetapan.modul') }}" class="nav-link {{ $r('tetapan.modul') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M17.5 14v7M14 17.5h7"/></svg>
                    Akses Modul
                </a>
                <a href="{{ route('tetapan.koperasi') }}" class="nav-link {{ $r('tetapan.koperasi') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                    Tetapan Koperasi
                </a>
                @if (auth()->user()->hasRole('super-user'))
                    <a href="{{ route('log.index') }}" class="nav-link {{ request()->routeIs('log.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Log Aktiviti
                    </a>
                @endif
            </div>
        </div>
        @endif

        <div class="side-foot">
            <div class="side-user">
                @php $cu = auth()->user(); @endphp
                <div class="avatar" style="overflow:hidden;">
                    @if ($cu && $cu->avatar_path)
                        <img src="{{ tenant_asset($cu->avatar_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr($cu->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="u-name">{{ $cu->name ?? 'Pengguna' }}</div>
                    <div class="u-role">{{ optional($cu?->roles->first())->name ?? 'Ahli' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:8px;">
                @csrf
                <button class="nav-link" type="submit" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 12H4M9 7l-5 5 5 5M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/></svg>
                    Log Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="scrim" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

    <!-- ===== Main ===== -->
    <div class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <button class="icon-btn hamburger" @click="sidebarOpen = !sidebarOpen">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div class="title-wrap">
                    <div class="crumb">@yield('crumb', 'Koperasi CMS')</div>
                    <h2>@yield('title', 'Dashboard')</h2>
                </div>
            </div>
            <div class="top-actions">
                <button class="icon-btn" title="Carian">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                </button>
                <button class="icon-btn" title="Notifikasi">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8a6 6 0 00-12 0c0 7-3 8-3 8h18s-3-1-3-8M13.7 21a2 2 0 01-3.4 0"/></svg>
                </button>
                <div x-data="{ buka: false }" style="position:relative;">
                    <button @click="buka = !buka" style="border:none;background:none;cursor:pointer;padding:0;border-radius:50%;">
                        <div class="avatar" title="{{ $cu->name ?? '' }}" style="overflow:hidden;">
                            @if ($cu && $cu->avatar_path)
                                <img src="{{ tenant_asset($cu->avatar_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ strtoupper(substr($cu->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                    </button>

                    <div x-show="buka" x-cloak @click.outside="buka = false" x-transition
                         style="position:absolute;right:0;top:48px;width:220px;background:var(--panel);border:1px solid var(--line);border-radius:12px;box-shadow:var(--shadow);overflow:hidden;z-index:50;">
                        <div style="padding:14px 16px;border-bottom:1px solid var(--line);">
                            <div style="font-weight:600;font-size:14px;">{{ $cu->name ?? 'Pengguna' }}</div>
                            <div style="font-size:12px;color:var(--muted);">{{ optional($cu?->roles->first())->name ?? 'Ahli' }}</div>
                        </div>
                        <a href="{{ route('profil.edit') }}" class="nav-link" style="border-radius:0;color:var(--ink);">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>
                            Profil Saya
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-link" type="submit" style="width:100%;border:none;background:none;cursor:pointer;text-align:left;border-radius:0;color:var(--danger);">
                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 12H4M9 7l-5 5 5 5M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/></svg>
                                Log Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="content-inner">
                @if (session('success'))
                    <div class="alert success">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert error">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

<!-- Alpine plugin collapse (untuk animasi buka/tutup) + core -->
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    // Confirm before destructive actions
    document.addEventListener('submit', function (e) {
        const f = e.target;
        if (f.dataset.confirm && !window.confirm(f.dataset.confirm)) {
            e.preventDefault();
        }
    });
    // Auto-dismiss flash messages
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            a.style.transition = 'opacity .5s, transform .5s';
            a.style.opacity = '0';
            a.style.transform = 'translateY(-8px)';
            setTimeout(() => a.remove(), 500);
        });
    }, 4500);
</script>
@stack('scripts')
</body>
</html>