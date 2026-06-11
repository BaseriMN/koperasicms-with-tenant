@extends('layouts.master')
@section('title', 'Sunting Kategori')
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Sunting Kategori</h1><p class="lead">Kemaskini {{ $kategori->nama }}.</p></div>
    <a href="{{ route('akaun.kategori.index', $jenis) }}" class="btn btn-ghost">Kembali</a>
</div>
<form method="POST" action="{{ route('akaun.kategori.update', [$jenis, $kategori]) }}">
    @csrf @method('PUT')
    @include('akaun.kategori._form', ['kategori' => $kategori])
    <div class="form-actions" style="border:0;max-width:620px;">
        <button class="btn btn-gold" type="submit">Kemaskini</button>
        <a href="{{ route('akaun.kategori.index', $jenis) }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
