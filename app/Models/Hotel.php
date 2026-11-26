<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Hotel extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'hoteles';

    protected $fillable = [
        'empresa_id', 'nombre', 'direccion', 'ciudad', 'telefono', 'email',
        'sitio_web', 'check_in', 'check_out', 'zona_horaria',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    // Hoteles donde puede trabajar el usuario: todos los de su empresa (scope) si es admin; si no, solo los asignados.
    public function scopePermitidos(Builder $query): Builder
    {
        $usuario = Auth::user();
        if ($usuario?->tieneRol('gerente', 'recepcionista')) {
            $query->whereIn($query->qualifyColumn('id'), fn ($q) => $q->select('hotel_id')->from('hotel_user')->where('user_id', $usuario->id));
        }

        return $query;
    }

    public const SESION = 'hotel_activo_id';

    /**
     * Hotel en el que trabaja el usuario: el elegido en la sesión o, si no hay o no le está
     * permitido, el primero que sí. Sin usuario: el primero.
     */
    public static function actual(): self
    {
        $hoteles = static::permitidos()->with('empresa')->orderBy('id');
        $elegido = session(self::SESION);

        $hotel = ($elegido ? (clone $hoteles)->find($elegido) : null) ?? $hoteles->first();
        if ($hotel) {
            return $hotel;
        }

        abort_if(Auth::user()?->tieneRol('gerente', 'recepcionista'), 403, 'No tienes ningún hotel asignado. Pide a tu administrador que te asigne uno.');

        return static::crearPorDefecto();
    }

    private static function crearPorDefecto(): self
    {
        // Empresa sin hoteles: se le crea el primero. Base vacía (instalación nueva): empresa y hotel de ejemplo.
        $empresa = Auth::user()?->empresa ?? Empresa::create(['nombre' => 'Hotel Buena Vida']);

        return $empresa->hoteles()->create(['nombre' => $empresa->nombre])->setRelation('empresa', $empresa);
    }

    // La marca (logo, nombre del club, RUC) es de la empresa; el hotel la expone para las vistas.
    public function getLogoUrlAttribute(): string
    {
        return $this->empresa->logo_url;
    }

    public function getEsloganAttribute(): string
    {
        return $this->empresa->nombre_club;
    }

    public function getRucAttribute(): ?string
    {
        return $this->empresa->ruc;
    }

    // "Hotel Buena Vida" -> "Buena Vida", para la barra lateral y la tarjeta.
    public function getNombreCortoAttribute(): string
    {
        return preg_replace('/^hotel\s+/iu', '', $this->nombre) ?: $this->nombre;
    }

    // Las columnas TIME vuelven como "14:00:00"; los inputs type="time" esperan "14:00".
    public function hora(string $campo): ?string
    {
        return $this->{$campo} ? substr($this->{$campo}, 0, 5) : null;
    }
}
