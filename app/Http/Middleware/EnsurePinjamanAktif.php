<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePinjamanAktif
{
    /**
     * Sekat akses modul pinjaman jika produk pinjaman dimatikan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! pinjaman_aktif()) {
            abort(404);
        }

        return $next($request);
    }
}