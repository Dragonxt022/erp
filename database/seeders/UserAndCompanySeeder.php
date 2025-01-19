<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InforUnidade;
use Illuminate\Support\Facades\Hash;

class UserAndCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Criando a unidade (empresa)
        $unidade = InforUnidade::create([
            'cep' => '12345-678',
            'cidade' => 'Cidade Teste',
            'bairro' => 'Bairro Teste',
            'rua' => 'Rua Teste',
            'numero' => '123',
            'cnpj' => '12.345.678/0001-99',
        ])


        // Criando o usuário
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'erp@erp.taiksu.com.br',
            'password' => Hash::make('12345678'),  // Senha segura
            'unidade_id' => $unidade->id,  // Referencia a unidade criada
            'pin' => '1234',  // PIN do usuário
            'cpf' => '123.456.789-00',  // CPF do usuário
        ]);


        // Atribuindo todas as permissões ao usuário
        $user->permissions()->attach($permissions->pluck('id')->toArray());

        // Criando um registro na tabela de detalhes do usuário (opcional)
        $user->userDetails()->create([
            'cep' => '12345-678',
            'cidade' => 'Cidade Teste',
            'bairro' => 'Bairro Teste',
            'rua' => 'Rua Teste',
            'numero' => '123',
        ]);
    }
}
