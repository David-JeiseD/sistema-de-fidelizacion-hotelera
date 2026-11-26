<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canje extends Model {
    use HasFactory;
    
    protected $fillable = ['hotel_id', 'cliente_id', 'recompensa_id', 'fecha', 'puntos'];

    public function hotel() {
        return $this->belongsTo(Hotel::class);
    }


    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }

    public function recompensa() {
        return $this->belongsTo(Recompensa::class);
    }
}
