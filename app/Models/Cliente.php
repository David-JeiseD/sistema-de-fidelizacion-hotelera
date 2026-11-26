<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Membresía: una persona inscrita en el club de una empresa. Los puntos viven aquí.
class Cliente extends Model {
    use PerteneceAEmpresa;

    use HasFactory;

    protected $fillable = ['empresa_id', 'persona_id'];

    public function empresa() {
        return $this->belongsTo(Empresa::class);
    }

    public function persona() {
        return $this->belongsTo(Persona::class);
    }

    public function visitas() {
        return $this->hasMany(Visita::class);
    }

    public function canjes() {
        return $this->hasMany(Canje::class);
    }

    public function getTotalPuntosAttribute() {
        return $this->visitas->sum('puntos') - $this->canjes->sum('puntos');
    }

    // Atajos para las vistas: los datos personales están en la persona global.
    public function getNombreAttribute(): ?string {
        return $this->persona?->nombre;
    }

    public function getDniAttribute(): ?string {
        return $this->persona?->numero_documento;
    }

    public function getTipoDocumentoAttribute(): ?string {
        return $this->persona?->tipo_documento;
    }

    // shortcut: busca solo por número; un pasaporte de 8 dígitos podría coincidir con un DNI. Pedir el tipo si llega a pasar.
    public function scopePorDocumento(Builder $query, ?string $numero): Builder {
        return $query->whereHas('persona', fn ($q) => $q->where('numero_documento', Persona::normalizar($numero)));
    }
}
