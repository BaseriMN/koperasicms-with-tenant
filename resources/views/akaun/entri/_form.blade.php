{{-- Partial borang entri. Jangkaan: $jenis, $categories, $members, $entri (boleh null) --}}
@php $e = $entri ?? null; @endphp

<div class="panel" style="max-width:680px;">
    <div class="panel-body">
        <div class="field">
            <label>Kategori</label>
            <select class="select" name="category_id" required>
                <option value="">— Pilih kategori —</option>
                @foreach ($categories as $cat)
                    @if ($cat->children->count())
                        {{-- Induk berkumpulan: pilih anak-anak --}}
                        <optgroup label="{{ $cat->nama }}">
                            @foreach ($cat->children as $child)
                                <option value="{{ $child->id }}" {{ (string) old('category_id', $e->category_id ?? '') === (string) $child->id ? 'selected' : '' }}>
                                    {{ $child->nama }}
                                </option>
                            @endforeach
                        </optgroup>
                    @else
                        <option value="{{ $cat->id }}" {{ (string) old('category_id', $e->category_id ?? '') === (string) $cat->id ? 'selected' : '' }}>
                            {{ $cat->nama }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('category_id') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label>Amaun (RM)</label>
                <input class="input" type="number" name="amaun" min="0.01" step="0.01" value="{{ old('amaun', $e->amaun ?? '') }}" required>
                @error('amaun') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Tarikh</label>
                <input class="input" type="date" name="tarikh" value="{{ old('tarikh', optional($e->tarikh ?? null)->toDateString() ?? now()->toDateString()) }}" required>
                @error('tarikh') <div class="err">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="field">
            <label>Ahli Berkaitan <span class="hint">(pilihan — cth yuran ahli)</span></label>
            <select class="select" name="member_id">
                <option value="">— Tiada / bukan berkaitan ahli —</option>
                @foreach ($members as $m)
                    <option value="{{ $m->id }}" {{ (string) old('member_id', $e->member_id ?? '') === (string) $m->id ? 'selected' : '' }}>
                        {{ $m->no_ahli }} — {{ $m->nama }}
                    </option>
                @endforeach
            </select>
            <div class="hint">Jika dipilih, rekod ini akan dipaut ke profil ahli berkenaan.</div>
        </div>

        <div class="grid grid-2">
            <div class="field">
                <label>No. Rujukan <span class="hint">(pilihan)</span></label>
                <input class="input" name="rujukan" value="{{ old('rujukan', $e->rujukan ?? '') }}" placeholder="Resit / baucar">
            </div>
            <div class="field">
                <label>{{ $jenis === 'pendapatan' ? 'Daripada (Pembayar)' : 'Kepada (Penerima)' }} <span class="hint">(pilihan)</span></label>
                <input class="input" name="penerima_pembayar" value="{{ old('penerima_pembayar', $e->penerima_pembayar ?? '') }}">
            </div>
        </div>

        <div class="field" style="margin-bottom:0;">
            <label>Keterangan <span class="hint">(pilihan)</span></label>
            <textarea class="textarea" name="keterangan">{{ old('keterangan', $e->keterangan ?? '') }}</textarea>
        </div>
    </div>
</div>
