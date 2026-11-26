<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Usuario solo para pruebas locales: php artisan db:seed --class=QaUserSeeder
class QaUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'qa@fidelhotel.test'],
            ['name' => 'QA Local', 'dni' => '00000001', 'password' => 'QaLocal-2026!']
        );
    }
}
