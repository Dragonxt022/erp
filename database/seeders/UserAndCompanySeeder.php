<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InforUnidade;
use App\Models\Permission; // Nome correto do model
use App\Models\Cargo; // Adicionando o modelo Cargo
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
        ]);

        // Buscando o cargo pelo nome (exemplo: 'Gerente')
        $cargo = Cargo::where('name', 'G')->first();  // 'G' é o nome do cargo, pode ser alterado conforme sua tabela

        // Verificando se o cargo existe antes de criar o usuário
        if (!$cargo) {
            // Caso o cargo não exista, você pode lançar uma exceção ou criar um fallback
            $cargo = Cargo::create(['name' => 'G']);  // Criando o cargo caso não tenha sido encontrado
        }

        // Criando o usuário
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'erp@erp.taiksu.com.br',
            'password' => Hash::make('12345678'),  // Senha segura
            'unidade_id' => $unidade->id,  // Referencia a unidade criada
            'cargo_id' => $cargo->id,  // Agora estamos referenciando o cargo pelo seu ID
            'pin' => '1234',  // PIN do usuário
            'cpf' => '123.456.789-00',  // CPF do usuário
        ]);

        // Pegando todas as permissões
        $permissions = Permission::all();

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
