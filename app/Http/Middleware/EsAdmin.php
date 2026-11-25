<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Deja pasar solo a administradores (usuarios, respaldo y borrados). */
class EsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->esAdmin()) {
            if ($request->expectsJson()) {
                abort(403);
            }
            return back(fallback: route('dashboard'))->withErrors('Solo un administrador puede hacer esto.');
        }
        return $next($request);
    }
}
