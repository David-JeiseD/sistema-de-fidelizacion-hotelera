<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodos_pago';

    protected $fillable = ['hotel_id', 'nombre', 'titular', 'numero', 'qr_path', 'activo'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    protected $casts = ['activo' => 'boolean'];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function getQrUrlAttribute(): string
    {
        return asset('storage/' . $this->qr_path);
    }
}
