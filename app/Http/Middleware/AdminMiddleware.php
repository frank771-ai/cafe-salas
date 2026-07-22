<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Restringe el panel y los reportes a cuentas con el rol administrador. */
class AdminMiddleware
{
    /** Detiene con HTTP 403 cualquier solicitud sin privilegios administrativos. */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_admin, 403, 'Esta sección requiere permisos de administración.');

        return $next($request);
    }
}
