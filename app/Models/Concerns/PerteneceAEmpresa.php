<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Aislamiento multi-local para modelos con columna empresa_id.
 * Con un usuario de una empresa en sesión, toda consulta Eloquent se limita a esa
 * empresa y los registros nuevos se crean en ella. Sin usuario (login, consola) o
 * con el super-admin (empresa_id null) no se filtra.
 * Ojo: las reglas de validación exists/unique y DB::table() no pasan por aquí.
 */
trait PerteneceAEmpresa
{
    protected static function bootPerteneceAEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $query) {
            if ($empresaId = Auth::user()?->empresa_id) {
                $query->where($query->qualifyColumn('empresa_id'), $empresaId);
            }
        });

        static::creating(function ($modelo) {
            $modelo->empresa_id ??= Auth::user()?->empresa_id;
        });
    }
}
