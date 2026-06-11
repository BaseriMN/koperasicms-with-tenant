@extends('layouts.master')
@section('title', 'Tambah Peranan')
@section('crumb', 'Peranan')

@section('content')
<div class="page-head">
    <div><h1>Tambah Peranan</h1><p class="lead">Cipta peranan baharu dan tetapkan kebenaran.</p></div>
    <a href="{{ route('roles.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="panel" style="max-width:680px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="field">
                <label>Nama Peranan</label>
                <input class="input" name="name" value="{{ old('name') }}" required>
                <div class="hint">Slug akan dijana automatik (cth: Admin Koperasi → admin-koperasi).</div>
                @error('name') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Penerangan</label>
                <textarea class="textarea" name="description">{{ old('description') }}</textarea>
            </div>
            @if ($permissions->count())
                <div class="field">
                    <label>Kebenaran</label>
                    <div class="check-grid">
                        @foreach ($permissions as $p)
                            <label class="check">
                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}"
                                    {{ in_array($p->id, old('permissions', [])) ? 'checked' : '' }}>
                                {{ $p->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Simpan Peranan</button>
                <a href="{{ route('roles.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
