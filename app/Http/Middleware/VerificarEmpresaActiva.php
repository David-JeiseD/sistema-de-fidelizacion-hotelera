<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Cierra la sesión si el usuario fue desactivado o su empresa está suspendida (los datos se conservan).
class VerificarEmpresaActiva
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();
        $motivo = match (true) {
            $usuario && ! $usuario->activo => 'Tu acceso fue desactivado. Habla con el administrador de tu empresa.',
            $usuario?->empresa?->estado === 'suspendida' => 'La cuenta de tu empresa está suspendida. Contacta a tu proveedor del sistema.',
            default => null,
        };

        if ($motivo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $motivo]);
        }

        return $next($request);
    }
}
