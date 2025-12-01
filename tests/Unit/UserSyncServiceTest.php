<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserSyncService;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\InforUnidade;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_unidade_details_creates_unidade()
    {
        $unidadeData = [
            'id' => 123,
            'cep' => '12345-678',
            'cidade' => 'Test City',
            'bairro' => 'Test Neighborhood',
            'rua' => 'Test Street',
            'numero' => '100',
            'cnpj' => '12.345.678/0001-90',
        ];

        UserSyncService::syncUnidadeDetails($unidadeData);

        $this->assertDatabaseHas('infor_unidades', [
            'id' => 123,
            'cidade' => 'Test City',
        ]);
    }

    public function test_sync_user_creates_user_and_permissions()
    {
        $userData = [
            'id' => 456,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'cpf' => '123.456.789-00',
            'grupo_nome' => 'Franqueado',
            'grupo_id' => 1,
            'foto' => 'photo.jpg',
        ];
        $unidadeId = 123;

        $user = UserSyncService::syncUser($userData, $unidadeId);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals(1, $user->franqueado);
        $this->assertEquals(0, $user->franqueadora);

        $this->assertDatabaseHas('users', [
            'id' => 456,
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('user_permissions', [
            'user_id' => 456,
            'controle_estoque' => 1, // Should be true for Franqueado
        ]);
    }
}
