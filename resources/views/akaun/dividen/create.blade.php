@extends('layouts.master')
@section('title', 'Pengiraan Dividen Baharu')
@section('crumb', 'Dividen')

@section('content')
<div class="page-head">
    <div><h1>Pengiraan Dividen Baharu</h1><p class="lead">Dividen dikira atas jumlah saham anggota × kadar diluluskan. Tabung dikira atas untung bersih.</p></div>
    <a href="{{ route('akaun.dividen.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="alert success" style="background:rgba(31,111,92,.08);border-color:rgba(31,111,92,.22);color:var(--teal-deep);">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7z"/></svg>
    <span>Selepas dicipta, anda boleh ubah tabung peruntukan & saham layak ahli sebelum dimuktamadkan.</span>
</div>

<div class="panel" style="max-width:680px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('akaun.dividen.store') }}">
            @csrf
            <div class="grid grid-2">
                <div class="field">
                    <label>Tahun Dividen</label>
                    <input class="input" type="number" name="tahun" value="{{ old('tahun', $tahunCadang) }}" min="2000" max="2100" required>
                    @error('tahun') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Untung Bersih Selepas Audit (RM)</label>
                    <input class="input" type="number" name="untung_bersih" step="0.01" min="0" value="{{ old('untung_bersih') }}" required>
                    <div class="hint">Asas pengiraan tabung (Rizab, KWAPK).</div>
                    @error('untung_bersih') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Tarikh Mula Tahun Kewangan</label>
                    <input class="input" type="date" name="tarikh_mula" value="{{ old('tarikh_mula', $mulaCadang) }}" required>
                    @error('tarikh_mula') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Tarikh Cut-off <span class="hint">(hujung tahun kewangan)</span></label>
                    <input class="input" type="date" name="tarikh_cutoff" value="{{ old('tarikh_cutoff', $cutoffCadang) }}" required>
                    <div class="hint">Saham selepas tarikh ini tak dikira.</div>
                    @error('tarikh_cutoff') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Jumlah Saham Anggota (RM)</label>
                    <input class="input" type="number" name="jumlah_saham_anggota" step="0.01" min="0" value="{{ old('jumlah_saham_anggota', $sahamCadang) }}" required>
                    <div class="hint">Auto dari lejar setakat cut-off. Boleh ubah ikut laporan audit.</div>
                    @error('jumlah_saham_anggota') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Kadar Dividen — Cadangan Juruaudit (%)</label>
                    <input class="input" type="number" name="peratus_auditor" step="0.01" min="0" max="100" value="{{ old('peratus_auditor', $peratusDividen) }}" required>
                    @error('peratus_auditor') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Kadar Dividen — Diluluskan Mesyuarat Agung (%)</label>
                    <input class="input" type="number" name="peratus_diluluskan" step="0.01" min="0" max="100" value="{{ old('peratus_diluluskan', $peratusDividen) }}" required>
                    <div class="hint">Kadar ini yang digunakan untuk pengiraan.</div>
                    @error('peratus_diluluskan') <div class="err">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field">
                <label>Catatan <span class="hint">(pilihan)</span></label>
                <textarea class="textarea" name="catatan">{{ old('catatan') }}</textarea>
            </div>

            {{-- Pratonton tabung default --}}
            <div class="field" style="margin-bottom:0;">
                <label>Tabung Peruntukan Default (boleh ubah selepas dicipta)</label>
                <div class="panel" style="box-shadow:none;">
                    <table>
                        <thead><tr><th>Tabung</th><th>Jenis</th><th style="text-align:right;">Nilai</th></tr></thead>
                        <tbody>
                            @foreach ($tabungDefault as $t)
                                <tr>
                                    <td>{{ $t['nama_tabung'] }}</td>
                                    <td>{{ $t['jenis_kira'] === 'peratus' ? 'Peratus' : 'Amaun Tetap' }}</td>
                                    <td style="text-align:right;">{{ $t['jenis_kira'] === 'peratus' ? $t['nilai'] . '%' : 'RM ' . number_format($t['nilai'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Cipta & Kira</button>
                <a href="{{ route('akaun.dividen.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
