{{-- Partial: medan borang ahli + waris. Jangkaan: $member (boleh null), $users --}}
@php $m = $member ?? null; @endphp

<div class="panel" style="margin-bottom:20px;">
    <div class="panel-head"><h3>Maklumat Ahli</h3></div>
    <div class="panel-body">
        <div class="grid grid-2">
            <div class="field">
                <label>Nama Penuh</label>
                <input class="input" name="nama" value="{{ old('nama', $m->nama ?? '') }}" required>
                @error('nama') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>No. Kad Pengenalan</label>
                <input class="input" name="no_kp" value="{{ old('no_kp', $m->no_kp ?? '') }}" placeholder="010101-01-0101">
                @error('no_kp') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Telefon</label>
                <input class="input" name="telefon" value="{{ old('telefon', $m->telefon ?? '') }}" placeholder="012-3456789">
            </div>
            <div class="field">
                <label>Akaun Pengguna (login) <span class="hint">(pilihan)</span></label>
                <select class="select" name="user_id">
                    <option value="">— Tiada akaun —</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ (string) old('user_id', $m->user_id ?? '') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Tarikh Sertai</label>
                <input class="input" type="date" name="tarikh_sertai" value="{{ old('tarikh_sertai', optional($m->tarikh_sertai ?? null)->toDateString() ?? now()->toDateString()) }}">
            </div>
            <div class="field">
                <label>Status</label>
                <select class="select" name="status">
                    @foreach (['aktif'=>'Aktif','tidak_aktif'=>'Tidak Aktif','berhenti'=>'Berhenti'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ old('status', $m->status ?? 'aktif') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field">
            <label>Alamat</label>
            <textarea class="textarea" name="alamat">{{ old('alamat', $m->alamat ?? '') }}</textarea>
        </div>
        <div class="field" style="margin-bottom:0;">
            <label>Foto Ahli <span class="hint">(pilihan — PNG/JPG, maks 2MB)</span></label>
            <div style="display:flex;gap:14px;align-items:center;">
                <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,var(--teal),var(--teal-deep));display:grid;place-items:center;color:#fff;font-size:24px;font-weight:600;">
                    @if (!empty($m) && $m->foto_path)
                        <img src="{{ tenant_asset($m->foto_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr($m->nama ?? 'A', 0, 1)) }}
                    @endif
                </div>
                <input class="input" type="file" name="foto" accept="image/*" style="flex:1;">
            </div>
            @error('foto') <div class="err">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3>Maklumat Waris</h3>
        <span class="badge teal">Waris Utama</span>
    </div>
    <div class="panel-body">
        @php $w = $m->nextOfKin ?? null; @endphp
        <div class="grid grid-2">
            <div class="field">
                <label>Nama Waris</label>
                <input class="input" name="waris_nama" value="{{ old('waris_nama', $w->nama ?? '') }}">
                <div class="hint">Isi jika ingin merekod waris.</div>
                @error('waris_nama') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Hubungan</label>
                <input class="input" name="waris_hubungan" value="{{ old('waris_hubungan', $w->hubungan ?? '') }}" placeholder="Isteri / Anak / Bapa">
                @error('waris_hubungan') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>No. KP Waris</label>
                <input class="input" name="waris_no_kp" value="{{ old('waris_no_kp', $w->no_kp ?? '') }}">
            </div>
            <div class="field">
                <label>Telefon Waris</label>
                <input class="input" name="waris_telefon" value="{{ old('waris_telefon', $w->telefon ?? '') }}">
            </div>
        </div>
        <div class="field" style="margin-bottom:0;">
            <label>Alamat Waris</label>
            <textarea class="textarea" name="waris_alamat">{{ old('waris_alamat', $w->alamat ?? '') }}</textarea>
        </div>
    </div>
</div>
