@extends('layouts.master')
@section('title', 'Tambah Kebenaran')
@section('crumb', 'Kebenaran')

@section('content')
<div class="page-head">
    <div><h1>Tambah Kebenaran</h1><p class="lead">Cipta kebenaran baharu untuk sistem.</p></div>
    <a href="{{ route('permissions.index') }}" class="btn btn-ghost">Kembali</a>
</div>
<div class="panel" style="max-width:600px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf
            <div class="field">
                <label>Nama Kebenaran</label>
                <input class="input" name="name" value="{{ old('name') }}" required>
                @error('name') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Penerangan</label>
                <textarea class="textarea" name="description">{{ old('description') }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Simpan</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
