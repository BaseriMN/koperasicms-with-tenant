{{-- Partial borang kategori. Jangkaan: $jenis, $parents, $kategori (boleh null) --}}
@php $k = $kategori ?? null; @endphp

<div class="panel" style="max-width:620px;">
    <div class="panel-body">
        <div class="field">
            <label>Nama Kategori</label>
            <input class="input" name="nama" value="{{ old('nama', $k->nama ?? '') }}" required>
            @error('nama') <div class="err">{{ $message }}</div> @enderror
        </div>
        <div class="grid grid-2">
            <div class="field">
                <label>Kod <span class="hint">(pilihan)</span></label>
                <input class="input" name="kod" value="{{ old('kod', $k->kod ?? '') }}" placeholder="P-001">
            </div>
            <div class="field">
                <label>Kategori Induk <span class="hint">(jika sub-kategori)</span></label>
                <select class="select" name="parent_id">
                    <option value="">— Tiada (kategori utama) —</option>
                    @foreach ($parents as $p)
                        <option value="{{ $p->id }}" {{ (string) old('parent_id', $k->parent_id ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-2">
            <div class="field">
                <label>Sifat</label>
                <select class="select" name="berulang">
                    <option value="0" {{ old('berulang', $k->berulang ?? false) ? '' : 'selected' }}>One-off (sekali)</option>
                    <option value="1" {{ old('berulang', $k->berulang ?? false) ? 'selected' : '' }}>Berulang (bulanan/tetap)</option>
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <select class="select" name="is_active">
                    <option value="1" {{ old('is_active', $k->is_active ?? true) ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $k->is_active ?? true) ? '' : 'selected' }}>Nyahaktif</option>
                </select>
            </div>
        </div>
        <div class="field" style="margin-bottom:0;">
            <label>Keterangan <span class="hint">(pilihan)</span></label>
            <textarea class="textarea" name="keterangan">{{ old('keterangan', $k->keterangan ?? '') }}</textarea>
        </div>
    </div>
</div>
