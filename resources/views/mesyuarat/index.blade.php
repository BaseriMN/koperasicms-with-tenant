@extends('layouts.master')
@section('title', 'Mesyuarat & Minit')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div><h1>Mesyuarat &amp; Minit</h1><p class="lead">Jadual mesyuarat koperasi dan rekod minit.</p></div>
    @if ($canCreate)
        <a href="{{ route('mesyuarat.create') }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Mesyuarat Baharu
        </a>
    @endif
</div>
<div class="grid grid-2">
    @forelse ($meetings as $m)
        <div class="panel"><div class="panel-body">
            @if ($m->tarikh->isFuture())
                <span class="badge teal"><span class="dot"></span>Akan Datang</span>
            @else
                <span class="badge"><span class="dot"></span>Selesai</span>
            @endif
            <h3 style="font-family:'Fraunces',serif;font-size:19px;margin:12px 0 8px;">{{ $m->tajuk }}</h3>
            <div class="cell-sub" style="display:flex;gap:16px;flex-wrap:wrap;">
                <span>📅 {{ $m->tarikh->translatedFormat('d M Y') }}</span>
                @if ($m->lokasi)<span>📍 {{ $m->lokasi }}</span>@endif
            </div>
            @if ($m->minit)
                <p class="cell-sub" style="margin-top:14px;line-height:1.6;">{{ Str::limit($m->minit, 140) }}</p>
            @endif
        </div></div>
    @empty
        <div class="empty" style="grid-column:1/-1;">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
            <div>Tiada mesyuarat dijadualkan.</div>
        </div>
    @endforelse
</div>
<div style="margin-top:18px;">{{ $meetings->links() }}</div>
@endsection
