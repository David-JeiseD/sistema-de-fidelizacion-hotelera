<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresaPorHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model {
    use PerteneceAEmpresaPorHotel;

    use HasFactory;
    protected $table = 'habitaciones'; 
    protected $fillable = ['hotel_id', 'nombre', 'puntos'];

    public function hotel() {
        return $this->belongsTo(Hotel::class);
    }

}
