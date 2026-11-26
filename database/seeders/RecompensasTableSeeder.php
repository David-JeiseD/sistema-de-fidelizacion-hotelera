<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecompensasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresaId = \App\Models\Hotel::actual()->empresa_id;
        DB::table('recompensas')->insert(array_map(fn ($r) => $r + ['empresa_id' => $empresaId], [
            [
                'nombre' => 'Vaso de Jugo',
                'puntos' => 40,
            ],
            [
                'nombre' => 'Jarra de Jugo',
                'puntos' => 90,
            ],
            [
                'nombre' => '2 Latas de Cerveza',
                'puntos' => 120,
            ],
            [
                'nombre' => 'Transporte del Aeropuerto al Hotel',
                'puntos' => 250,
            ],
            [
                'nombre' => 'Desayuno',
                'puntos' => 300,
            ],
            [
                'nombre' => 'Botella de Vino',
                'puntos' => 600,
            ],
            [
                'nombre' => '1 Noche Gratis en Habitación Matrimonial Premium',
                'puntos' => 1000,
            ],
        ]));
    }
}
