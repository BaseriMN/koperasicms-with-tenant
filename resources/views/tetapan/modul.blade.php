@extends('layouts.master')
@section('title', 'Akses Modul')
@section('crumb', 'Tetapan Sistem')

@push('head')
<style>
    .matrix-wrap { overflow-x:auto; }
    table.matrix { width:100%; border-collapse:separate; border-spacing:0; min-width:680px; }
    table.matrix th, table.matrix td { padding:14px 16px; border-bottom:1px solid var(--line); }
    table.matrix thead th {
        position:sticky; top:0; background:#fff; text-align:center; vertical-align:bottom;
        font-size:12px; letter-spacing:.04em; color:var(--ink-2); font-weight:600; text-transform:none;
    }
    table.matrix thead th .mh { display:flex; flex-direction:column; align-items:center; gap:8px; }
    table.matrix thead th .ico {
        width:38px; height:38px; border-radius:10px; display:grid; place-items:center;
        background:linear-gradient(135deg,var(--teal),var(--teal-deep)); color:#fff;
    }
    table.matrix thead th .ico svg { width:18px; height:18px; }
    table.matrix tbody th {
        text-align:left; font-family:'Fraunces',serif; font-size:15px; font-weight:600;
        background:#faf8f2; white-space:nowrap;
    }
    table.matrix tbody th .slug { display:block; font-family:'Outfit',sans-serif; font-size:11px; color:var(--muted); font-weight:500; margin-top:2px; }
    table.matrix td { text-align:center; }
    table.matrix tbody tr:hover td, table.matrix tbody tr:hover th { background:#f4f1ea; }

    /* Toggle switch */
    .sw { position:relative; display:inline-block; width:44px; height:25px; cursor:pointer; }
    .sw input { opacity:0; width:0; height:0; }
    .sw .track { position:absolute; inset:0; background:#d8d2c4; border-radius:999px; transition:.2s; }
    .sw .track::before { content:''; position:absolute; width:19px; height:19px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.25); }
    .sw input:checked + .track { background:linear-gradient(135deg,var(--gold),var(--gold-soft)); }
    .sw input:checked + .track::before { transform:translateX(19px); }

    .tool-link { background:none; border:none; color:var(--teal); font:inherit; font-size:11px; font-weight:600; cursor:pointer; text-decoration:underline; padding:0; }
    .legend { display:flex; gap:18px; align-items:center; font-size:13px; color:var(--muted); margin-top:16px; flex-wrap:wrap; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1>Akses Modul</h1>
        <p class="lead">Kawal modul mana setiap peranan boleh akses. Tukar suis, kemudian simpan.</p>
    </div>
    <a href="{{ route('roles.index') }}" class="btn btn-ghost">Kembali ke Tetapan</a>
</div>

<div class="alert success" style="background:rgba(31,111,92,.08);border-color:rgba(31,111,92,.22);color:var(--teal-deep);">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7z"/></svg>
    <span><strong>Super User</strong> sentiasa mempunyai akses penuh ke semua modul dan tidak boleh dikunci — jadi ia tidak disenaraikan di bawah.</span>
</div>

<form method="POST" action="{{ route('tetapan.modul.update') }}">
    @csrf @method('PUT')

    <div class="panel">
        <div class="panel-body matrix-wrap">
            <table class="matrix">
                <thead>
                    <tr>
                        <th style="text-align:left;vertical-align:bottom;">Peranan</th>
                        @foreach ($modules as $key => $mod)
                            <th>
                                <div class="mh">
                                    <span class="ico"><svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">{!! $mod['icon'] !!}</svg></span>
                                    <span>{{ $mod['label'] }}</span>
                                    <button type="button" class="tool-link" onclick="toggleCol('{{ $key }}')">semua</button>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <th>
                                {{ $role->name }}
                                <span class="slug">{{ $role->slug }}</span>
                                <button type="button" class="tool-link" onclick="toggleRow('{{ $role->id }}')">tanda semua</button>
                            </th>
                            @foreach ($modules as $key => $mod)
                                <td>
                                    <label class="sw">
                                        <input type="checkbox"
                                            name="access[{{ $role->id }}][]"
                                            value="{{ $key }}"
                                            data-row="{{ $role->id }}"
                                            data-col="{{ $key }}"
                                            {{ isset($current[$role->id][$key]) ? 'checked' : '' }}>
                                        <span class="track"></span>
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="legend">
                <span><span class="badge gold" style="vertical-align:middle;">ON</span> peranan boleh akses modul</span>
                <span><span class="badge" style="vertical-align:middle;">OFF</span> tersembunyi & dihalang</span>
            </div>
        </div>
    </div>

    <div class="form-actions" style="border:0;padding-top:22px;">
        <button class="btn btn-gold" type="submit">Simpan Akses</button>
        <a href="{{ route('tetapan.modul') }}" class="btn btn-ghost">Set Semula</a>
    </div>
</form>

@push('scripts')
<script>
    function toggleRow(roleId) {
        const boxes = document.querySelectorAll('input[data-row="' + roleId + '"]');
        const allOn = [...boxes].every(b => b.checked);
        boxes.forEach(b => b.checked = !allOn);
    }
    function toggleCol(key) {
        const boxes = document.querySelectorAll('input[data-col="' + key + '"]');
        const allOn = [...boxes].every(b => b.checked);
        boxes.forEach(b => b.checked = !allOn);
    }
</script>
@endpush
@endsection
