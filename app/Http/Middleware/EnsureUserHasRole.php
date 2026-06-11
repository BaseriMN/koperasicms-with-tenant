<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Benarkan akses jika pengguna mempunyai mana-mana role yang disenaraikan.
     * Penggunaan: ->middleware('role:super-user,admin,kerani')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // super-user sentiasa dibenarkan
        if ($user->hasRole('super-user')) {
            return $next($request);
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
