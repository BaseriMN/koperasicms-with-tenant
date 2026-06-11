@extends('layouts.master')
@section('title', 'Mesyuarat Baharu')
@section('crumb', 'Mesyuarat & Minit')

@section('content')
<div class="page-head">
    <div><h1>Mesyuarat Baharu</h1><p class="lead">Jadualkan mesyuarat dan rekod minit.</p></div>
    <a href="{{ route('mesyuarat.index') }}" class="btn btn-ghost">Kembali</a>
</div>
<div class="panel" style="max-width:680px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('mesyuarat.store') }}">
            @csrf
            <div class="field">
                <label>Tajuk Mesyuarat</label>
                <input class="input" name="tajuk" value="{{ old('tajuk') }}" required>
                @error('tajuk') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>Tarikh</label>
                    <input class="input" type="date" name="tarikh" value="{{ old('tarikh') }}" required>
                    @error('tarikh') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Lokasi</label>
                    <input class="input" name="lokasi" value="{{ old('lokasi') }}">
                </div>
            </div>
            <div class="field">
                <label>Minit / Catatan</label>
                <textarea class="textarea" name="minit">{{ old('minit') }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Simpan Mesyuarat</button>
                <a href="{{ route('mesyuarat.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
