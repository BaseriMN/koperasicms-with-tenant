@extends('layouts.master')
@section('title', 'Sunting Ahli')
@section('crumb', 'Keahlian')

@section('content')
<div class="page-head">
    <div>
        <h1>Sunting Ahli</h1>
        <p class="lead">No. Ahli <span class="badge gold" style="font-family:'Fraunces',serif;">{{ $member->no_ahli }}</span> — nombor ini kekal walaupun maklumat berubah.</p>
    </div>
    <a href="{{ route('members.show', $member) }}" class="btn btn-ghost">Kembali</a>
</div>

<form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('members._form', ['member' => $member, 'users' => $users])
    <div class="form-actions" style="border:0;">
        <button class="btn btn-gold" type="submit">Kemaskini</button>
        <a href="{{ route('members.show', $member) }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
