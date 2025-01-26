<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuário Franqueadora
        DB::table('users')->insert([
            'name' => 'Admin Franqueadora',
            'email' => 'franqueadora@empresa.com',
            'pin' => '1234', // Apenas para exemplo; ajuste conforme necessário
            'password' => Hash::make('12345678'),
            'cpf' => '123.456.789-00',
            'franqueadora' => true,
            'franqueado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Usuário Franqueado
        DB::table('users')->insert([
            'name' => 'Admin Franqueado',
            'email' => 'franqueado@empresa.com',
            'pin' => '1212', // Apenas para exemplo; ajuste conforme necessário
            'password' => Hash::make('12345678'),
            'cpf' => '123.456.789-10',
            'franqueadora' => false,
            'franqueado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Bruno',
            'email' => 'pissinatti2019@gmail.com',
            'pin' => '1313', // Apenas para exemplo; ajuste conforme necessário
            'password' => Hash::make('12345678'),
            'cpf' => '022.794.582-41',
            'franqueadora' => false,
            'franqueado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
