<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model {
    use HasFactory;
    
    
    protected $fillable = ['nombre', 'dni', 'total_puntos'];

    public function visitas() {
        return $this->hasMany(Visita::class);
    }

    public function canjes() {
        return $this->hasMany(Canje::class);
    }

    public function getTotalPuntosAttribute() {
        return $this->visitas->sum('puntos') - $this->canjes->sum('puntos');
    }
}
