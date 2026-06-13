<?php

if (! function_exists('wang')) {
    /**
     * Bulatkan nilai wang ke 2 titik perpuluhan.
     * Guna untuk SEMUA pengiraan wang bagi elak ralat floating-point
     * (cth RM50000 menjadi 49999.99).
     */
    function wang(float|int|string|null $nilai): float
    {
        return round((float) $nilai, 2);
    }
}


if (! function_exists('simpanan_aktif')) {
    /**
     * Adakah produk simpanan diaktifkan untuk koperasi ini?
     */
    function simpanan_aktif(): bool
    {
        return \App\Models\Setting::get('produk_simpanan', '0') === '1';
    }
}

// DEFAULT PINJAMAN OFF
if (! function_exists('pinjaman_aktif')) {
    /**
     * Adakah produk pinjaman diaktifkan untuk koperasi ini?
     */
    function pinjaman_aktif(): bool
    {
        return \App\Models\Setting::get('produk_pinjaman', '0') === '1';
    }
}


if (! function_exists('simpan_imej_mampat')) {
    /**
     * Simpan imej yang dimuat naik selepas resize + mampat ke WebP.
     */
    function simpan_imej_mampat(
        \Illuminate\Http\UploadedFile $fail,
        string $folder,
        int $maxSisi = 600,
        int $kualiti = 85
    ): string {
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $imej = $manager->decode($fail->getRealPath());
        $imej->scaleDown(width: $maxSisi, height: $maxSisi);

        $data = (string) $imej->encode(
            new \Intervention\Image\Encoders\WebpEncoder(quality: $kualiti)
        );

        $namaFail = $folder . '/' . \Illuminate\Support\Str::random(40) . '.webp';
        \Illuminate\Support\Facades\Storage::disk('public')->put($namaFail, $data);

        return $namaFail;
    }
}
