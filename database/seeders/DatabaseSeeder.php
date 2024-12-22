<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Chama o seeder de permissões
        $this->call(PermissionsSeeder::class);

        // Chama o seeder de cargos
        $this->call(CargoSeeder::class);

        // Chama o seeder de empresa e usuário
        $this->call(UserAndCompanySeeder::class);
    }
}
