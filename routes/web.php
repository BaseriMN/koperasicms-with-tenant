<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes — domain SaaS sahaja (koperasicms.site / localhost)
|--------------------------------------------------------------------------
| Tiada data koperasi di sini. App koperasi penuh ada di routes/tenant.php
*/

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', fn () => 'KoperasiCMS Central — sistem hidup. 🟢');
    });
}