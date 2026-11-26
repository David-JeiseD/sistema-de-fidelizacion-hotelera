<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Uso en rutas: ->middleware('rol:gerente,admin_empresa')
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $usuario = $request->user();

        // El super-admin no opera hoteles: se le lleva a su panel en vez de mostrarle un error.
        if ($usuario->esSuperAdmin() && ! in_array('super_admin', $roles, true)) {
            return redirect()->route('plataforma');
        }

        abort_unless($usuario->tieneRol(...$roles), 403, 'Tu rol no tiene acceso a esta sección.');

        return $next($request);
    }
}
