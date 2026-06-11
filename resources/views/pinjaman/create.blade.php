@extends('layouts.master')
@section('title', 'Permohonan Pinjaman')
@section('crumb', 'Pinjaman')

@section('content')
<div class="page-head">
    <div><h1>Permohonan Pinjaman</h1><p class="lead">Rekod permohonan pinjaman bagi pihak ahli koperasi.</p></div>
    <a href="{{ route('pinjaman.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="panel" style="max-width:680px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('pinjaman.store') }}"
              x-data="{ q: '', pilih: '{{ old('member_id') }}' }">
            @csrf

            {{-- Pilih ahli --}}
            <div class="field">
                <label>Ahli Pemohon</label>
                <input class="input" type="text" x-model="q" placeholder="Taip untuk tapis no ahli / nama..." style="margin-bottom:8px;">
                <select class="select" name="member_id" x-model="pilih" required size="6" style="height:auto;">
                    <template x-for="m in $store.members.filter(x => (x.no_ahli + ' ' + x.nama).toLowerCase().includes(q.toLowerCase()))" :key="m.id">
                        <option :value="m.id" x-text="m.no_ahli + ' — ' + m.nama"></option>
                    </template>
                </select>
                @error('member_id') <div class="err">{{ $message }}</div> @enderror
                <div class="hint">Hanya ahli berstatus <strong>aktif</strong> dipaparkan.</div>
            </div>

            <div class="grid grid-2">
                <div class="field">
                    <label>Jumlah Pinjaman (RM)</label>
                    <input class="input" type="number" name="amount" step="0.01" min="100" value="{{ old('amount') }}" required>
                    @error('amount') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Tempoh Bayaran (bulan)</label>
                    <input class="input" type="number" name="tempoh" min="1" max="120" value="{{ old('tempoh') }}" required>
                    @error('tempoh') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field" style="margin-bottom:0;">
                <label>Tujuan Pinjaman</label>
                <textarea class="textarea" name="tujuan" required placeholder="Nyatakan tujuan pinjaman...">{{ old('tujuan') }}</textarea>
                @error('tujuan') <div class="err">{{ $message }}</div> @enderror
            </div>

            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Hantar Permohonan</button>
                <a href="{{ route('pinjaman.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('members', @json($members->map(fn($m) => ['id' => $m->id, 'no_ahli' => $m->no_ahli, 'nama' => $m->nama])));
    });
</script>
@endsection