@extends('layouts.master')
@section('title', 'Pindah Milik Keahlian')
@section('crumb', 'Keahlian')

@section('content')
<div class="page-head">
    <div>
        <h1>Pindah Milik Keahlian</h1>
        <p class="lead">No. Ahli <span class="badge gold" style="font-family:'Fraunces',serif;">{{ $member->no_ahli }}</span> akan <strong>kekal</strong>. Hanya pemilik & maklumat peribadi bertukar.</p>
    </div>
    <a href="{{ route('members.show', $member) }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="panel">
        <div class="panel-head"><h3>Pemilik Semasa</h3></div>
        <div class="panel-body">
            <div class="field"><label>Nama</label><div>{{ $member->nama }}</div></div>
            <div class="field"><label>No. KP</label><div>{{ $member->no_kp ?? '—' }}</div></div>
            <div class="field" style="margin-bottom:0;"><label>Telefon</label><div>{{ $member->telefon ?? '—' }}</div></div>
        </div>
    </div>
    <div class="panel" style="border-color:var(--gold-soft);">
        <div class="panel-head"><h3>Pemilik Baharu</h3><span class="badge gold">Baharu</span></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('member.pindah', $member) }}">
                @csrf
                <div class="field">
                    <label>Nama Pemilik Baharu</label>
                    <input class="input" name="to_nama" value="{{ old('to_nama') }}" required>
                    @error('to_nama') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>No. KP</label>
                    <input class="input" name="to_no_kp" value="{{ old('to_no_kp') }}">
                </div>
                <div class="field">
                    <label>Telefon</label>
                    <input class="input" name="to_telefon" value="{{ old('to_telefon') }}">
                </div>
                <div class="field">
                    <label>Akaun Login <span class="hint">(pilihan)</span></label>
                    <select class="select" name="to_user_id">
                        <option value="">— Tiada akaun —</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ (string) old('to_user_id') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Alamat</label>
                    <textarea class="textarea" name="to_alamat">{{ old('to_alamat') }}</textarea>
                </div>
                <div class="grid grid-2">
                    <div class="field">
                        <label>Tarikh Pindah</label>
                        <input class="input" type="date" name="tarikh_pindah" value="{{ old('tarikh_pindah', now()->toDateString()) }}" required>
                        @error('tarikh_pindah') <div class="err">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label>Sebab <span class="hint">(pilihan)</span></label>
                        <input class="input" name="sebab" value="{{ old('sebab') }}" placeholder="Kematian / Serahan">
                    </div>
                </div>

                {{-- Kelulusan Mesyuarat ALK --}}
                <div style="border-top:1px solid var(--line);margin:8px 0 18px;padding-top:18px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                        <span class="badge teal">Kelulusan Mesyuarat ALK</span>
                        <span class="hint">Wajib diluluskan dalam mesyuarat lembaga.</span>
                    </div>
                    <div class="field">
                        <label>Mesyuarat Kelulusan</label>
                        <select class="select" name="meeting_id" required>
                            <option value="">— Pilih mesyuarat —</option>
                            @foreach ($meetings as $mt)
                                <option value="{{ $mt->id }}" {{ (string) old('meeting_id') === (string) $mt->id ? 'selected' : '' }}>{{ $mt->tajuk }} ({{ \Illuminate\Support\Carbon::parse($mt->tarikh)->format('d/m/Y') }})</option>
                            @endforeach
                        </select>
                        @error('meeting_id') <div class="err">{{ $message }}</div> @enderror
                    </div>
                    <div class="grid grid-2">
                        <div class="field">
                            <label>Pencadang</label>
                            <select class="select" name="pencadang_id" required>
                                <option value="">— Pilih ahli —</option>
                                @foreach ($ahliList as $a)
                                    <option value="{{ $a->id }}" {{ (string) old('pencadang_id') === (string) $a->id ? 'selected' : '' }}>{{ $a->no_ahli }} — {{ $a->nama }}</option>
                                @endforeach
                            </select>
                            @error('pencadang_id') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        <div class="field">
                            <label>Penyokong</label>
                            <select class="select" name="penyokong_id" required>
                                <option value="">— Pilih ahli —</option>
                                @foreach ($ahliList as $a)
                                    <option value="{{ $a->id }}" {{ (string) old('penyokong_id') === (string) $a->id ? 'selected' : '' }}>{{ $a->no_ahli }} — {{ $a->nama }}</option>
                                @endforeach
                            </select>
                            @error('penyokong_id') <div class="err">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Catatan Kelulusan</label>
                        <textarea class="textarea" name="catatan_kelulusan" required placeholder="cth: Diluluskan dalam Mesyuarat ALK Bil. 3/2025.">{{ old('catatan_kelulusan') }}</textarea>
                        @error('catatan_kelulusan') <div class="err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit" data-confirm="Sahkan pindah milik keahlian {{ $member->no_ahli }}? {{ simpanan_aktif() ? 'Saham & simpanan' : 'Saham' }} kekal pada nombor ahli ini.">Proses Pindah Milik</button>
                    <a href="{{ route('members.show', $member) }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
