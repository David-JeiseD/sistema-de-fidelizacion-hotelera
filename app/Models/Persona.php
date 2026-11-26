<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Identidad global del huésped: existe una sola vez en toda la plataforma.
class Persona extends Model
{
    public const TIPOS = [
        'DNI' => ['DNI', '/^\d{8}$/', '8 dígitos'],
        'CE' => ['Carné de extranjería', '/^[A-Z0-9]{9,12}$/', '9 a 12 caracteres'],
        'PASAPORTE' => ['Pasaporte', '/^[A-Z0-9]{6,12}$/', '6 a 12 caracteres'],
    ];

    protected $fillable = ['tipo_documento', 'numero_documento', 'nombre'];

    public function membresias(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public static function normalizar(?string $numero): string
    {
        return strtoupper(preg_replace('/[\s.-]/', '', (string) $numero));
    }
}
