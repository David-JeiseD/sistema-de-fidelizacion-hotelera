<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Si la empresa del usuario está suspendida, se cierra su sesión (los datos se conservan).
class VerificarEmpresaActiva
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->empresa?->estado === 'suspendida') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'La cuenta de tu empresa está suspendida. Contacta a tu proveedor del sistema.']);
        }

        return $next($request);
    }
}
