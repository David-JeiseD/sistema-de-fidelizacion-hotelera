<?php

namespace Tests;

use App\Models\Cliente;
use App\Models\Hotel;
use App\Models\Persona;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Miembro del club de la empresa del hotel dado (por defecto, el hotel actual).
    protected function miembro(string $documento, string $nombre = 'Ana', ?Hotel $hotel = null, string $tipo = 'DNI'): Cliente
    {
        $hotel ??= Hotel::actual();
        $persona = Persona::firstOrCreate(['tipo_documento' => $tipo, 'numero_documento' => $documento], ['nombre' => $nombre]);

        return Cliente::create(['empresa_id' => $hotel->empresa_id, 'persona_id' => $persona->id]);
    }
}
