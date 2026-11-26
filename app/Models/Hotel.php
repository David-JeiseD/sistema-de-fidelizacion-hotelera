<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public const SESION = 'hotel_activo_id';

    /**
     * Hotel en el que trabaja el usuario: el elegido en la sesión o, si no hay o no es de
     * su empresa (el scope lo descarta), el primero de la empresa. Sin usuario: el primero.
     */
    public static function actual(): self
    {
        $hoteles = static::with('empresa')->orderBy('id');
        $elegido = session(self::SESION);

        return ($elegido ? (clone $hoteles)->find($elegido) : null)
            ?? $hoteles->first()
            ?? static::crearPorDefecto();
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
