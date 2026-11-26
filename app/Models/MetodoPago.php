<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodos_pago';

    protected $fillable = ['nombre', 'titular', 'numero', 'qr_path', 'activo'];

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
