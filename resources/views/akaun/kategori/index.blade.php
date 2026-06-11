@extends('layouts.master')
@section('title', 'Kategori ' . ucfirst($jenis))
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Kategori {{ ucfirst($jenis) }}</h1><p class="lead">Urus kategori & sub-kategori {{ $jenis }} secara dinamik.</p></div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('akaun.entri.index', $jenis) }}" class="btn btn-ghost">Kembali</a>
        <a href="{{ route('akaun.kategori.create', $jenis) }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Kategori
        </a>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Senarai Kategori</h3><span class="badge">{{ $categories->count() }} kategori utama</span></div>
    <table>
        <thead><tr><th>Nama</th><th>Kod</th><th>Sifat</th><th>Rekod</th><th>Status</th><th style="text-align:right;">Tindakan</th></tr></thead>
        <tbody>
            @forelse ($categories as $cat)
                <tr>
                    <td style="font-weight:600;">{{ $cat->nama }}</td>
                    <td class="cell-sub">{{ $cat->kod ?? '—' }}</td>
                    <td>
                        @if ($cat->berulang)<span class="badge teal">Berulang</span>@else<span class="badge">One-off</span>@endif
                    </td>
                    <td class="cell-sub">{{ $cat->entries_count }}</td>
                    <td>@if($cat->is_active)<span class="badge ok"><span class="dot"></span>Aktif</span>@else<span class="badge off"><span class="dot"></span>Nyahaktif</span>@endif</td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;">
                            <a href="{{ route('akaun.kategori.edit', [$jenis, $cat]) }}" class="btn btn-ghost btn-sm">Sunting</a>
                            <form method="POST" action="{{ route('akaun.kategori.destroy', [$jenis, $cat]) }}" data-confirm="Padam / nyahaktif kategori ini?">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Padam</button>
                            </form>
                        </div>
                    </td>
                </tr>
                {{-- Sub-kategori --}}
                @foreach ($cat->children as $child)
                    <tr style="background:#faf8f2;">
                        <td style="padding-left:42px;">
                            <span style="color:var(--muted);">›</span> {{ $child->nama }}
                        </td>
                        <td class="cell-sub">{{ $child->kod ?? '—' }}</td>
                        <td>@if ($child->berulang)<span class="badge teal">Berulang</span>@else<span class="badge">One-off</span>@endif</td>
                        <td class="cell-sub">{{ $child->entries_count }}</td>
                        <td>@if($child->is_active)<span class="badge ok"><span class="dot"></span>Aktif</span>@else<span class="badge off"><span class="dot"></span>Nyahaktif</span>@endif</td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:8px;">
                                <a href="{{ route('akaun.kategori.edit', [$jenis, $child]) }}" class="btn btn-ghost btn-sm">Sunting</a>
                                <form method="POST" action="{{ route('akaun.kategori.destroy', [$jenis, $child]) }}" data-confirm="Padam / nyahaktif sub-kategori ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Padam</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="6"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                    <div>Tiada kategori lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
