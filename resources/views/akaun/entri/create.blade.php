@extends('layouts.master')
@section('title', 'Rekod ' . ucfirst($jenis))
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Rekod {{ ucfirst($jenis) }}</h1><p class="lead">Masukkan satu rekod {{ $jenis }} baharu.</p></div>
    <a href="{{ route('akaun.entri.index', $jenis) }}" class="btn btn-ghost">Kembali</a>
</div>
<form method="POST" action="{{ route('akaun.entri.store', $jenis) }}">
    @csrf
    @include('akaun.entri._form', ['entri' => null])
    <div class="form-actions" style="border:0;max-width:680px;">
        <button class="btn btn-gold" type="submit">Simpan</button>
        <a href="{{ route('akaun.entri.index', $jenis) }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
