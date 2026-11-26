<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionSeeder extends Seeder
{
    public function run()
    {
        $hotelId = \App\Models\Hotel::actual()->id;
        DB::table('habitaciones')->insert(array_map(fn ($h) => $h + ['hotel_id' => $hotelId], [
            ['nombre' => 'Personal', 'puntos' => 40],
            ['nombre' => 'Matrimonial Básica', 'puntos' => 50],
            ['nombre' => 'Matrimonial Premium', 'puntos' => 60],
            ['nombre' => 'Doble', 'puntos' => 110],
        ]));
    }
}