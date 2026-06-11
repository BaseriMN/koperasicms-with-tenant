@extends('layouts.master')
@section('title', 'Sunting Peranan')
@section('crumb', 'Peranan')

@section('content')
<div class="page-head">
    <div><h1>Sunting Peranan</h1><p class="lead">Kemaskini {{ $role->name }}.</p></div>
    <a href="{{ route('roles.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="panel" style="max-width:680px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="field">
                <label>Nama Peranan</label>
                <input class="input" name="name" value="{{ old('name', $role->name) }}" required>
                @error('name') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Penerangan</label>
                <textarea class="textarea" name="description">{{ old('description', $role->description) }}</textarea>
            </div>
            @if ($permissions->count())
                <div class="field">
                    <label>Kebenaran</label>
                    @php $assigned = $role->permissions->pluck('id')->toArray(); @endphp
                    <div class="check-grid">
                        @foreach ($permissions as $p)
                            <label class="check">
                                <input type="checkbox" name="permissions[]" value="{{ $p->id }}"
                                    {{ in_array($p->id, old('permissions', $assigned)) ? 'checked' : '' }}>
                                {{ $p->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Kemaskini</button>
                <a href="{{ route('roles.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
