<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model {
    use HasFactory;
    
    protected $fillable = ['cliente_id', 'habitacion_id', 'fecha', 'puntos'];

    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }

    public function habitacion() {
        return $this->belongsTo(Habitacion::class);
    }
}
