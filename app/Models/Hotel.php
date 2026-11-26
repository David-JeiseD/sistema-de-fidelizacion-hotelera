<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotel extends Model
{
    protected $table = 'hoteles';

    protected $fillable = [
        'empresa_id', 'nombre', 'direccion', 'ciudad', 'telefono', 'email',
        'sitio_web', 'check_in', 'check_out', 'zona_horaria',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Hotel en el que trabaja el usuario actual.
     * shortcut: toma el primer hotel de su empresa; la fase 2 agrega el selector de hotel activo en sesión.
     */
    public static function actual(): self
    {
        $empresaId = auth()->user()?->empresa_id;

        return static::with('empresa')
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('id')
            ->first()
            ?? static::crearPorDefecto();
    }

    private static function crearPorDefecto(): self
    {
        $empresa = Empresa::create(['nombre' => 'Hotel Buena Vida']);

        return $empresa->hoteles()->create(['nombre' => 'Hotel Buena Vida'])->setRelation('empresa', $empresa);
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
