@extends('layouts.master')
@section('title', 'Sunting Kebenaran')
@section('crumb', 'Kebenaran')

@section('content')
<div class="page-head">
    <div><h1>Sunting Kebenaran</h1><p class="lead">Kemaskini {{ $permission->name }}.</p></div>
    <a href="{{ route('permissions.index') }}" class="btn btn-ghost">Kembali</a>
</div>
<div class="panel" style="max-width:600px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf @method('PUT')
            <div class="field">
                <label>Nama Kebenaran</label>
                <input class="input" name="name" value="{{ old('name', $permission->name) }}" required>
                @error('name') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Penerangan</label>
                <textarea class="textarea" name="description">{{ old('description', $permission->description) }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Kemaskini</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
