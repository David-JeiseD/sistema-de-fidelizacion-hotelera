<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionSeeder extends Seeder
{
    public function run()
    {
        DB::table('habitaciones')->insert([
            ['nombre' => 'Personal', 'puntos' => 40],
            ['nombre' => 'Matrimonial Básica', 'puntos' => 50],
            ['nombre' => 'Matrimonial Premium', 'puntos' => 60],
            ['nombre' => 'Doble', 'puntos' => 110],
        ]);
    }
}