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