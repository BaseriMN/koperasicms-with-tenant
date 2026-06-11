@php
    use App\Models\Setting;
    $namaPendek   = Setting::get('nama_pendek', 'Koperasi');
    $namaKoperasi = Setting::get('nama_koperasi', 'Koperasi');
    $logoPath     = Setting::get('logo_path', '');
@endphp


<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Masuk — {{ $namaKoperasi }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0c1f1c; --teal-deep:#0f433a; --gold:#c0962c; --gold-soft:#e3c976; --line:#e1dccf; --muted:#7c8783; --danger:#b1402f; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Outfit',sans-serif; color:var(--ink); }
        .wrap { display:grid; grid-template-columns:1.05fr .95fr; min-height:100vh; }

        /* Left: brand panel */
        .hero {
            background: linear-gradient(150deg, #0c1f1c 0%, #0f433a 55%, #11302b 100%);
            color:#fff; padding:54px; display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden;
        }
        .hero::before { content:''; position:absolute; width:480px; height:480px; border-radius:50%; right:-160px; top:-120px;
            background:radial-gradient(circle, rgba(192,150,44,.28), transparent 70%); }
        .hero::after { content:''; position:absolute; width:340px; height:340px; border-radius:50%; left:-120px; bottom:-100px;
            background:radial-gradient(circle, rgba(31,111,92,.5), transparent 70%); }
        .hero-brand { display:flex; align-items:center; gap:13px; position:relative; z-index:2; }
        .hero-brand .mark { width:48px; height:48px; border-radius:13px; background:linear-gradient(135deg,var(--gold),var(--gold-soft));
            display:grid; place-items:center; font-family:'Fraunces',serif; font-weight:700; color:var(--ink); font-size:23px; }
        .hero-brand .name { font-family:'Fraunces',serif; font-size:19px; }
        .hero-brand .sub { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--gold-soft); }
        .hero-mid { position:relative; z-index:2; }
        .hero-mid h1 { font-family:'Fraunces',serif; font-size:46px; line-height:1.08; font-weight:600; max-width:9ch; }
        .hero-mid p { margin-top:18px; color:#aebbb7; font-size:16px; max-width:38ch; line-height:1.6; }
        .hero-foot { position:relative; z-index:2; font-size:12.5px; color:#7e8e8a; }

        /* Right: form */
        .formside { display:grid; place-items:center; padding:40px; background:#f4f1ea; }
        .card { width:100%; max-width:400px; }
        .card h2 { font-family:'Fraunces',serif; font-size:28px; }
        .card .tag { color:var(--muted); font-size:14px; margin-top:6px; margin-bottom:30px; }
        .field { margin-bottom:18px; }
        .field label { display:block; font-size:13px; font-weight:600; margin-bottom:7px; }
        .input { width:100%; padding:13px 15px; border:1px solid var(--line); border-radius:11px; font:inherit; font-size:14.5px; background:#fff; transition:all .16s; }
        .input:focus { outline:none; border-color:var(--gold); box-shadow:0 0 0 3px rgba(192,150,44,.15); }
        .row { display:flex; align-items:center; justify-content:space-between; margin:6px 0 24px; font-size:13px; }
        .row label { display:flex; align-items:center; gap:8px; color:var(--muted); cursor:pointer; }
        .row input { accent-color:var(--gold); }
        .btn { width:100%; padding:14px; border:none; border-radius:11px; font:inherit; font-size:15px; font-weight:600; cursor:pointer;
            background:var(--ink); color:#fff; transition:all .18s; }
        .btn:hover { background:var(--teal-deep); transform:translateY(-1px); }
        .err { color:var(--danger); font-size:12.5px; margin-top:6px; }
        .alert-e { background:rgba(177,64,47,.07); border:1px solid rgba(177,64,47,.25); color:var(--danger); padding:12px 15px; border-radius:11px; font-size:13.5px; margin-bottom:22px; }
        @media (max-width:880px){ .wrap{ grid-template-columns:1fr; } .hero{ display:none; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="hero-brand">
<div class="hero-brand">
            @if ($logoPath)
                <div class="mark" style="background:#fff;padding:4px;overflow:hidden;">
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
                </div>
            @else
                <div class="mark">{{ strtoupper(substr($namaPendek, 0, 1)) }}</div>
            @endif
            <div><div class="name">{{ $namaPendek }}</div><div class="sub">CMS Portal</div></div>
        </div>
        </div>
        <div class="hero-mid">
            <h1>Urus koperasi dengan yakin.</h1>
            <p>Portal bersepadu untuk pengurusan ahli, simpanan, pinjaman, mesyuarat dan audit kewangan — semua di satu tempat.</p>
        </div>
        <div class="hero-foot">© {{ date('Y') }} {{ $namaKoperasi }}. Hak cipta terpelihara BaseriMN</div>
    </div>

    <div class="formside">
        <div class="card">
            <h2>Selamat kembali</h2>
            <p class="tag">Log masuk ke akaun koperasi anda.</p>

            @if (session('error'))
                <div class="alert-e">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="field">
                    <label for="email">Alamat Email</label>
                    <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@koperasi.com" required autofocus>
                    @error('email') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">Kata Laluan</label>
                    <input class="input" id="password" type="password" name="password" placeholder="••••••••" required>
                    @error('password') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <label><input type="checkbox" name="remember"> Ingat saya</label>
                </div>
                <button class="btn" type="submit">Log Masuk</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
