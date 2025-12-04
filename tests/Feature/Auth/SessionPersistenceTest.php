<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_login_with_remember_me()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'cpf' => '123.456.789-00',
        ]);

        $response = $this->post('/entrar', [
            'cpf' => '123.456.789-00',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // Assert that the remember_web cookie is present
        $response->assertCookie(Auth::getRecallerName(), vsprintf('%s|%s|%s', [
            $user->id,
            $user->getRememberToken(),
            $user->password,
        ]));
    }

    public function test_session_lifetime_config()
    {
        $this->assertEquals(525600, config('session.lifetime'));
    }
}
