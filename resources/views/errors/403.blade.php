<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'Outfit',sans-serif;
            min-height:100vh; display:grid; place-items:center;
            background: linear-gradient(150deg, #0c1f1c 0%, #0f433a 60%, #11302b 100%);
            color:#fff; text-align:center; padding:30px; position:relative; overflow:hidden;
        }
        body::before { content:''; position:absolute; width:520px; height:520px; border-radius:50%; right:-180px; top:-160px;
            background:radial-gradient(circle, rgba(192,150,44,.22), transparent 70%); }
        .box { position:relative; z-index:2; max-width:460px; }
        .code { font-family:'Fraunces',serif; font-size:120px; font-weight:700; line-height:1;
            background:linear-gradient(135deg,#c0962c,#e3c976); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        h1 { font-family:'Fraunces',serif; font-size:28px; margin:10px 0 12px; }
        p { color:#aebbb7; font-size:15px; line-height:1.6; margin-bottom:28px; }
        .btn { display:inline-block; padding:13px 24px; border-radius:11px; text-decoration:none;
            background:linear-gradient(135deg,#c0962c,#e3c976); color:#0c1f1c; font-weight:600; transition:all .18s; }
        .btn:hover { filter:brightness(1.06); transform:translateY(-2px); }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">403</div>
        <h1>Akses Ditolak</h1>
        <p>{{ $exception->getMessage() ?: 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini. Sila hubungi pentadbir koperasi jika anda rasa ini satu kesilapan.' }}</p>
        <a href="{{ url('/dashboard') }}" class="btn">Kembali ke Dashboard</a>
    </div>
</body>
</html>
