@extends('layouts.master')
@section('title', 'Daftar Ahli')
@section('crumb', 'Keahlian')

@section('content')
<div class="page-head">
    <div>
        <h1>Daftar Ahli Baharu</h1>
        <p class="lead">Nombor ahli akan dijana automatik: <span class="badge gold" style="font-family:'Fraunces',serif;">{{ $nextNoAhli }}</span></p>
    </div>
    <a href="{{ route('members.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
    @csrf
    @include('members._form', ['member' => null, 'users' => $users])
    <div class="form-actions" style="border:0;">
        <button class="btn btn-gold" type="submit">Simpan Ahli</button>
        <a href="{{ route('members.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
