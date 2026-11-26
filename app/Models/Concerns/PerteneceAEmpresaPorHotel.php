<?php

namespace App\Models\Concerns;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Aislamiento multi-local para modelos con columna hotel_id: se ven los registros de
 * todos los hoteles de la empresa del usuario (una cadena comparte historial), y los
 * nuevos se crean en el hotel activo. Mismas excepciones que PerteneceAEmpresa.
 */
trait PerteneceAEmpresaPorHotel
{
    protected static function bootPerteneceAEmpresaPorHotel(): void
    {
        static::addGlobalScope('empresa', function (Builder $query) {
            if ($empresaId = Auth::user()?->empresa_id) {
                // Subconsulta con el query builder: no dispara el scope de Hotel (evita recursión).
                $query->whereIn($query->qualifyColumn('hotel_id'), fn ($q) => $q->select('id')->from('hoteles')->where('empresa_id', $empresaId));
            }
        });

        static::creating(function ($modelo) {
            if (! $modelo->hotel_id && Auth::check()) {
                $modelo->hotel_id = Hotel::actual()->id;
            }
        });
    }
}
