<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Quien compra el sistema: una cadena o un hotel individual (empresa con un solo hotel).
class Empresa extends Model
{
    protected $fillable = ['nombre', 'razon_social', 'ruc', 'nombre_club', 'logo_path', 'max_hoteles', 'estado'];

    protected $attributes = ['nombre_club' => 'Club de huéspedes', 'max_hoteles' => 1, 'estado' => 'activa'];

    public function hoteles(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function recompensas(): HasMany
    {
        return $this->hasMany(Recompensa::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Logo subido por la empresa, o el logo original del proyecto si no hay uno.
    public function getLogoUrlAttribute(): string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : asset('images/logo.jpg');
    }
}
