<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function edit()
    {
        $palettes = config('themes.palettes');

        $current = [
            'nama_koperasi'  => Setting::get('nama_koperasi', ''),
            'nama_pendek'    => Setting::get('nama_pendek', 'Koperasi'),
            'no_pendaftaran' => Setting::get('no_pendaftaran', ''),
            'logo_path'      => Setting::get('logo_path', ''),
            'tema_palet'     => Setting::get('tema_palet', config('themes.default')),
            'tema_mode'      => Setting::get('tema_mode', 'light'),
            'produk_simpanan'=> Setting::get('produk_simpanan', '0'),
            'produk_pinjaman'=> Setting::get('produk_pinjaman', '0'),
        ];

        return view('tetapan.koperasi', compact('palettes', 'current'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_koperasi'  => ['required', 'string', 'max:150'],
            'nama_pendek'    => ['required', 'string', 'max:40'],
            'no_pendaftaran' => ['nullable', 'string', 'max:60'],
            'tema_palet'     => ['required', Rule::in(array_keys(config('themes.palettes')))],
            'tema_mode'      => ['required', Rule::in(['light', 'dark'])],
            'produk_simpanan'=> ['nullable'],
            'produk_pinjaman'=> ['nullable'],
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'], // 2MB
        ]);

        $pairs = [
            'nama_koperasi'  => $data['nama_koperasi'],
            'nama_pendek'    => $data['nama_pendek'],
            'no_pendaftaran' => $data['no_pendaftaran'] ?? '',
            'tema_palet'     => $data['tema_palet'],
            'tema_mode'      => $data['tema_mode'],
            'produk_simpanan'=> $request->has('produk_simpanan') ? '1' : '0',
            'produk_pinjaman'=> $request->has('produk_pinjaman') ? '1' : '0',
        ];

        // Muat naik logo baharu (jika ada) — buang logo lama
        if ($request->hasFile('logo')) {
            $lama = Setting::get('logo_path');
            if ($lama && Storage::disk('public')->exists($lama)) {
                Storage::disk('public')->delete($lama);
            }
            $pairs['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        Setting::putMany($pairs);

        return redirect()->route('tetapan.koperasi')
            ->with('success', 'Tetapan koperasi berjaya dikemaskini.');
    }

    public function buangLogo()
    {
        $lama = Setting::get('logo_path');
        if ($lama && Storage::disk('public')->exists($lama)) {
            Storage::disk('public')->delete($lama);
        }
        Setting::put('logo_path', '');

        return redirect()->route('tetapan.koperasi')
            ->with('success', 'Logo berjaya dibuang. Sistem kembali guna lambang huruf.');
    }
}