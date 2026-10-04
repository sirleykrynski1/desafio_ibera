<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRolUsuario
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! in_array($user->rol, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Acceso denegado: No cuentas con los permisos requeridos para acceder a este portal.');
        }

        return $next($request);
    }
}
