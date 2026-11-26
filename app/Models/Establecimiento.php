<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    protected $table = 'establecimiento';

    protected $fillable = [
        'nombre', 'eslogan', 'logo_path', 'ruc', 'direccion', 'ciudad',
        'telefono', 'email', 'sitio_web', 'check_in', 'check_out',
    ];

    // Registro único del hotel; se crea con valores por defecto si falta.
    public static function actual(): self
    {
        return static::query()->first()
            ?? static::create(['nombre' => 'Hotel Buena Vida', 'eslogan' => 'Club de huéspedes']);
    }

    // Logo subido por el local, o el logo original del proyecto si no hay uno.
    public function getLogoUrlAttribute(): string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : asset('images/logo.jpg');
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
