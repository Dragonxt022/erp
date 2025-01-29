<?php

namespace Database\Seeders;


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

        // Chama o seeder dos métodos de pagamento

        // $this->call(UserSeeder::class);
        // $this->call(DefaultPaymentMethodsSeeder::class);
        // $this->call(DefaultCanaisVendasSeeder::class);
        $this->call(ContasSeeder::class);
    }
}
