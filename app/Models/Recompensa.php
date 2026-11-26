<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recompensa extends Model {
    use HasFactory;
    
    protected $fillable = ['empresa_id', 'hotel_id', 'nombre', 'puntos', 'excluye_puntos'];

    public function hotel() {
        return $this->belongsTo(Hotel::class);
    }

    // Catálogo de la empresa que vale en este hotel: las generales (sin hotel) y las exclusivas del local.
    public function scopeDisponiblesEn($query, Hotel $hotel) {
        return $query->where('empresa_id', $hotel->empresa_id)
            ->where(fn ($q) => $q->whereNull('hotel_id')->orWhere('hotel_id', $hotel->id));
    }

}
