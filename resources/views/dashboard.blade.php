@extends('layouts.master')
@section('title', 'Dashboard')
@section('crumb', 'Utama')

@section('content')
@php
    $moduleMeta = config('modules.modules');
@endphp

<div class="page-head">
    <div>
        <h1>Selamat datang, {{ explode(' ', $user->name)[0] }}.</h1>
        <p class="lead">Ringkasan aktiviti koperasi anda hari ini, {{ now()->translatedFormat('d F Y') }}.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @foreach ($roles as $slug)
            <span class="badge gold"><span class="dot"></span>{{ $slug }}</span>
        @endforeach
    </div>
</div>

<div class="grid grid-3" style="margin-bottom:26px;">
    <div class="stat"><div class="k">Jumlah Staff</div><div class="v" id="s1" data-target="{{ $stats['staff'] }}">0</div><div class="meta">▲ aktif dalam sistem</div></div>
    <div class="stat"><div class="k">Jumlah Ahli</div><div class="v" id="s2" data-target="{{ $stats['ahli'] }}">0</div><div class="meta">▲ aktif dalam sistem</div></div>
    <div class="stat"><div class="k">Jumlah Saham Terkumpul</div><div class="v">RM <span id="s3" data-target="{{ (int) $stats['jumlah_saham_terkumpul'] }}">0</span></div><div class="meta">▲ keseluruhan koperasi</div></div>
    @if (simpanan_aktif())
    <div class="stat"><div class="k">Simpanan Terkumpul</div><div class="v">RM <span id="s4" data-target="{{ (int) $stats['simpanan'] }}">0</span></div><div class="meta">▲ keseluruhan koperasi</div></div>
    @endif
    @if (pinjaman_aktif())
    <div class="stat"><div class="k">Pinjaman Menunggu</div><div class="v" id="s5" data-target="{{ $stats['pinjaman_pending'] }}">0</div><div class="meta" style="color:var(--gold);">menunggu kelulusan</div></div>
    <div class="stat"><div class="k">Pinjaman Telah Diluluskan</div><div class="v">RM <span id="s6" data-target="{{ (int) $stats['pinjaman_approved'] }}">0</span></div><div class="meta" style="color:var(--gold);">telah kelulusan</div></div>
    @endif
</div>

<div class="panel-head" style="border:0;padding:0 0 16px;">
    <h3 style="font-size:18px;">Modul Anda</h3>
    <span class="badge teal">{{ count($allowedModules) }} modul tersedia</span>
</div>

<div class="tiles">
    @forelse ($allowedModules as $mod)
        @php 
            $m = $moduleMeta[$mod] ?? null;
            // Handle module dengan params
            if ($m && isset($m['route_params'])) {
                $url = route($m['route'], $m['route_params']);
            } elseif ($m && isset($m['route'])) {
                $url = route($m['route']);
            } else {
                $url = '#';
            }
        @endphp
        @if ($m)
            <a href="{{ $url }}" class="tile">
                <div class="ico">
                    <svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        {!! $m['icon'] !!}
                    </svg>
                </div>
                <h4>{{ $m['label'] }}</h4>
                <p>{{ $m['desc'] }}</p>
            </a>
        @endif
    @empty
        <div class="empty" style="grid-column:1/-1;">Tiada modul tersedia untuk peranan anda.</div>
    @endforelse
</div>

@push('scripts')
<script>
    // Count-up animation reading real values from data-target
    function countTo(el) {
        if (!el) return;
        const target = parseInt(el.dataset.target || '0', 10);
        if (target === 0) { el.textContent = '0'; return; }
        let cur = 0; const step = target / 40;
        const t = setInterval(() => {
            cur += step;
            if (cur >= target) { cur = target; clearInterval(t); }
            el.textContent = Math.floor(cur).toLocaleString('en-MY');
        }, 22);
    }
    ['s1','s2','s3', 's4', 's5', 's6'].forEach(id => countTo(document.getElementById(id)));
</script>
@endpush
@endsection
