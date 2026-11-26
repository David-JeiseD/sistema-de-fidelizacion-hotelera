<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresaPorHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model {
    use PerteneceAEmpresaPorHotel;

    use HasFactory;
    
    protected $fillable = ['hotel_id', 'cliente_id', 'habitacion_id', 'fecha', 'puntos'];

    public function hotel() {
        return $this->belongsTo(Hotel::class);
    }


    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }

    public function habitacion() {
        return $this->belongsTo(Habitacion::class);
    }
}
