@extends('layouts.master')
@section('title', 'Sunting ' . ucfirst($jenis))
@section('crumb', 'Akaun')

@section('content')
<div class="page-head">
    <div><h1>Sunting {{ ucfirst($jenis) }}</h1><p class="lead">Kemaskini rekod {{ $jenis }}.</p></div>
    <a href="{{ route('akaun.entri.index', $jenis) }}" class="btn btn-ghost">Kembali</a>
</div>
<form method="POST" action="{{ route('akaun.entri.update', [$jenis, $entri]) }}">
    @csrf @method('PUT')
    @include('akaun.entri._form', ['entri' => $entri])
    <div class="form-actions" style="border:0;max-width:680px;">
        <button class="btn btn-gold" type="submit">Kemaskini</button>
        <a href="{{ route('akaun.entri.index', $jenis) }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
