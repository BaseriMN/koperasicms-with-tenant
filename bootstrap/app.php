<?php

use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsurePinjamanAktif;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware
        $middleware->alias([
            'role'   => EnsureUserHasRole::class,    // role:super-user,admin
            'module' => EnsureModuleAccess::class,   // module:pengurusan_ahli
            'pinjaman_aktif' => EnsurePinjamanAktif::class,
        ]);

        // Cloudflare di depan — percaya proxy supaya dapat IP pelawat sebenar
        // (untuk central_activity_logs). SELAMAT sebab firewall sekat port 443 ke CF sahaja.
        $middleware->trustProxies(at: '*', headers:
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();