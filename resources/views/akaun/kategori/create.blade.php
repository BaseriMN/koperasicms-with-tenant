@extends('layouts.master')
@section('title', 'Tambah Kategori')
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Tambah Kategori {{ ucfirst($jenis) }}</h1><p class="lead">Cipta kategori atau sub-kategori baharu.</p></div>
    <a href="{{ route('akaun.kategori.index', $jenis) }}" class="btn btn-ghost">Kembali</a>
</div>
<form method="POST" action="{{ route('akaun.kategori.store', $jenis) }}">
    @csrf
    @include('akaun.kategori._form', ['kategori' => null])
    <div class="form-actions" style="border:0;max-width:620px;">
        <button class="btn btn-gold" type="submit">Simpan Kategori</button>
        <a href="{{ route('akaun.kategori.index', $jenis) }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
