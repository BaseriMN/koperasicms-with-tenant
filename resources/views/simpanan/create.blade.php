@extends('layouts.master')
@section('title', 'Rekod Transaksi')
@section('crumb', 'Simpanan & Saham')

@section('content')
<div class="page-head">
    <div><h1>Rekod Transaksi</h1><p class="lead">Tambah simpanan atau saham untuk ahli.</p></div>
    <a href="{{ route('simpanan.index') }}" class="btn btn-ghost">Kembali</a>
</div>
<div class="panel" style="max-width:600px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('simpanan.store') }}">
            @csrf
            <div class="field">
                <label>Ahli</label>
                <select class="select" name="user_id" required>
                    <option value="">— Pilih ahli —</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" {{ old('user_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
                @error('user_id') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Jenis</label>
                <select class="select" name="jenis" required>
                    <option value="simpanan" {{ old('jenis')==='simpanan' ? 'selected' : '' }}>Simpanan</option>
                    <option value="saham" {{ old('jenis')==='saham' ? 'selected' : '' }}>Saham</option>
                </select>
            </div>
            <div class="field">
                <label>Amaun (RM)</label>
                <input class="input" type="number" name="amaun" min="1" step="0.01" value="{{ old('amaun') }}" required>
                @error('amaun') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Rekod</button>
                <a href="{{ route('simpanan.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
