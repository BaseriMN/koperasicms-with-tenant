@extends('layouts.master')
@section('title', 'Tetapan Koperasi')
@section('crumb', 'Tetapan Sistem')

@push('head')
<style>
    .logo-preview {
        width: 96px; height: 96px; border-radius: 18px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--gold), var(--gold-soft));
        display: grid; place-items: center; overflow: hidden;
        font-family: 'Fraunces', serif; font-weight: 700; color: var(--ink); font-size: 42px;
        box-shadow: 0 8px 22px -8px rgba(192,150,44,.6);
    }
    .logo-preview img { width: 100%; height: 100%; object-fit: contain; background: #fff; }

    .palette-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .palette {
        border: 2px solid var(--line); border-radius: 12px; padding: 14px; cursor: pointer;
        transition: all .16s; background: #fff; position: relative;
    }
    .palette:hover { border-color: var(--gold-soft); }
    .palette input { position: absolute; opacity: 0; }
    .palette.sel { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(192,150,44,.15); }
    .palette .swatches { display: flex; gap: 5px; margin-bottom: 9px; }
    .palette .sw { width: 26px; height: 26px; border-radius: 7px; border: 1px solid rgba(0,0,0,.06); }
    .palette .pname { font-size: 13px; font-weight: 600; }

    .mode-toggle { display: flex; gap: 10px; }
    .mode-opt {
        flex: 1; border: 2px solid var(--line); border-radius: 12px; padding: 14px; cursor: pointer;
        text-align: center; transition: all .16s; background: #fff; font-size: 14px; font-weight: 600;
    }
    .mode-opt input { position: absolute; opacity: 0; }
    .mode-opt.sel { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(192,150,44,.15); }
    @media (max-width: 920px) { .palette-grid { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('content')
<div class="page-head">
    <div><h1>Tetapan Koperasi</h1><p class="lead">Ubah identiti koperasi: logo, nama, no pendaftaran & tema warna.</p></div>
    <a href="{{ route('tetapan.modul') }}" class="btn btn-ghost">Kembali ke Tetapan</a>
</div>

<form method="POST" action="{{ route('tetapan.koperasi.update') }}" enctype="multipart/form-data"
      x-data="{ palet: '{{ $current['tema_palet'] }}', mode: '{{ $current['tema_mode'] }}' }">
    @csrf @method('PUT')

    {{-- Identiti --}}
    <div class="panel" style="margin-bottom:20px;">
        <div class="panel-head"><h3>Identiti Koperasi</h3></div>
        <div class="panel-body">
            <div style="display:flex;gap:22px;align-items:flex-start;flex-wrap:wrap;">
                {{-- Logo --}}
                <div style="text-align:center;">
                    <div class="logo-preview">
                        @if ($current['logo_path'])
                            <img src="{{ tenant_asset($current['logo_path']) }}" alt="Logo">
                        @else
                            {{ strtoupper(substr($current['nama_pendek'] ?: 'K', 0, 1)) }}
                        @endif
                    </div>
                    @if ($current['logo_path'])
                        <button form="form-buang-logo" class="tool-link" type="submit"
                                style="margin-top:10px;background:none;border:none;color:var(--danger);font-size:12px;cursor:pointer;text-decoration:underline;">
                            Buang logo
                        </button>
                    @endif
                </div>
                {{-- Medan --}}
                <div style="flex:1;min-width:260px;">
                    <div class="field">
                        <label>Nama Penuh Koperasi</label>
                        <input class="input" name="nama_koperasi" value="{{ old('nama_koperasi', $current['nama_koperasi']) }}" required>
                        @error('nama_koperasi') <div class="err">{{ $message }}</div> @enderror
                    </div>
                    <div class="grid grid-2">
                        <div class="field">
                            <label>Nama Pendek <span class="hint">(papar di sidebar)</span></label>
                            <input class="input" name="nama_pendek" value="{{ old('nama_pendek', $current['nama_pendek']) }}" required>
                            @error('nama_pendek') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div class="field">
                            <label>No. Pendaftaran SKM</label>
                            <input class="input" name="no_pendaftaran" value="{{ old('no_pendaftaran', $current['no_pendaftaran']) }}" placeholder="cth: A-1-0123">
                        </div>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Muat Naik Logo Baharu <span class="hint">(PNG/JPG/SVG/WEBP, maks 2MB)</span></label>
                        <input class="input" type="file" name="logo" accept="image/*">
                        @error('logo') <div class="err">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tema --}}
    <div class="panel" style="margin-bottom:20px;">
        {{-- Produk & Modul --}}
    <div class="panel" style="margin-bottom:20px;">
        <div class="panel-head"><h3>Produk Koperasi</h3></div>
        <div class="panel-body">
            <label class="check" style="max-width:420px;">
                <input type="checkbox" name="produk_simpanan" value="1" {{ $current['produk_simpanan']==='1' ? 'checked' : '' }}>
                <div>
                    <div style="font-weight:600;">Aktifkan Produk Simpanan</div>
                    <div class="hint" style="margin-top:2px;">Jika dimatikan, sistem hanya menguruskan saham. Pilihan simpanan disembunyikan di borang, dashboard & profil ahli.</div>
                </div>
            </label>

            <label class="check" style="max-width:420px;margin-top:12px;">
                <input type="checkbox" name="produk_pinjaman" value="1" {{ $current['produk_pinjaman']==='1' ? 'checked' : '' }}>
                <div>
                    <div style="font-weight:600;">Aktifkan Produk Pinjaman</div>
                    <div class="hint" style="margin-top:2px;">Jika dimatikan, modul pinjaman disembunyikan & akses dihalang sepenuhnya.</div>
                </div>
            </label>
        </div>
    </div>
        <div class="panel-head"><h3>Tema Warna</h3><span class="badge gold">Berkesan seluruh sistem</span></div>
        <div class="panel-body">
            <div class="field">
                <label>Mod Paparan</label>
                <div class="mode-toggle">
                    <label class="mode-opt" :class="{ 'sel': mode==='light' }">
                        <input type="radio" name="tema_mode" value="light" x-model="mode"> ☀️ Light
                    </label>
                    <label class="mode-opt" :class="{ 'sel': mode==='dark' }">
                        <input type="radio" name="tema_mode" value="dark" x-model="mode"> 🌙 Dark
                    </label>
                </div>
            </div>

            <div class="field" style="margin-bottom:0;">
                <label>Palet Warna</label>
                <div class="palette-grid">
                    @foreach ($palettes as $key => $p)
                        @php $c = $p['light']; @endphp
                        <label class="palette" :class="{ 'sel': palet==='{{ $key }}' }">
                            <input type="radio" name="tema_palet" value="{{ $key }}" x-model="palet">
                            <div class="swatches">
                                <span class="sw" style="background:{{ $c['teal-deep'] }}"></span>
                                <span class="sw" style="background:{{ $c['teal'] }}"></span>
                                <span class="sw" style="background:{{ $c['gold'] }}"></span>
                                <span class="sw" style="background:{{ $c['gold-soft'] }}"></span>
                            </div>
                            <div class="pname">{{ $p['label'] }}</div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions" style="border:0;">
        <button class="btn btn-gold" type="submit">Simpan Tetapan</button>
        <a href="{{ route('tetapan.koperasi') }}" class="btn btn-ghost">Set Semula</a>
    </div>
</form>

{{-- Borang berasingan untuk buang logo (elak nested form) --}}
<form id="form-buang-logo" method="POST" action="{{ route('tetapan.koperasi.logo.buang') }}"
      data-confirm="Buang logo semasa?">
    @csrf @method('DELETE')
</form>
@endsection