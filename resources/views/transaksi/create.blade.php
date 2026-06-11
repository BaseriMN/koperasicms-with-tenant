@extends('layouts.master')
@section('title', 'Rekod Transaksi')
@section('crumb', 'Lejar Transaksi')

@section('content')
<div class="page-head">
    <div><h1>Rekod Transaksi</h1><p class="lead">Masukkan atau keluarkan {{ simpanan_aktif() ? 'saham / simpanan' : 'saham' }} ahli.</p></div>
    <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="panel" style="max-width:640px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('transaksi.store') }}">
            @csrf
            <div class="field">
                <label>Ahli</label>
                <select class="select" name="member_id" required>
                    <option value="">— Pilih ahli —</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" {{ (string) old('member_id', request('member')) === (string) $m->id ? 'selected' : '' }}>{{ $m->no_ahli }} — {{ $m->nama }}</option>
                    @endforeach
                </select>
                @error('member_id') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>Jenis</label>
                    <select class="select" name="jenis" required>
                        @if (simpanan_aktif())
                            <option value="simpanan" {{ old('jenis')==='simpanan' ? 'selected' : '' }}>Simpanan</option>
                        @endif
                        <option value="saham" {{ old('jenis')==='saham' ? 'selected' : '' }}>Saham</option>
                    </select>
                </div>
                <div class="field">
                    <label>Arah</label>
                    <select class="select" name="arah" required>
                        <option value="masuk" {{ old('arah')==='masuk' ? 'selected' : '' }}>Masuk (Kredit)</option>
                        <option value="keluar" {{ old('arah')==='keluar' ? 'selected' : '' }}>Keluar (Debit)</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Amaun (RM)</label>
                <input class="input" type="number" name="amaun" min="0.01" step="0.01" value="{{ old('amaun') }}" required>
                @error('amaun') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>No. Rujukan <span class="hint">(pilihan)</span></label>
                <input class="input" name="rujukan" value="{{ old('rujukan') }}" placeholder="Resit / rujukan">
            </div>
            <div class="field">
                <label>Keterangan <span class="hint">(pilihan)</span></label>
                <textarea class="textarea" name="keterangan">{{ old('keterangan') }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Rekod</button>
                <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
