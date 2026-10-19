<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post('/registerproses', [
            'username' => 'testuser1',
            'email' => 'testuser1@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'username' => 'testuser1',
            'email' => 'testuser1@example.com',
            'role' => 'user',
        ]);
    }

    public function test_registration_fails_with_duplicate_username()
    {
        User::factory()->create(['username' => 'existinguser']);

        $response = $this->post('/registerproses', [
            'username' => 'existinguser',
            'email' => 'different@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_registration_fails_with_reserved_username()
    {
        $response = $this->post('/registerproses', [
            'username' => 'edit',
            'email' => 'edit@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_registration_fails_with_weak_password()
    {
        $response = $this->post('/registerproses', [
            'username' => 'shortpassuser',
            'email' => 'short@example.com',
            'password' => '123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_regenerates_session()
    {
        $user = User::factory()->create([
            'username' => 'validlogin',
            'password' => Hash::make('SecretPass123!'),
            'role' => 'user',
        ]);

        $this->get('/login');
        $sessionIdBefore = session()->getId();

        $response = $this->post('/login', [
            'username' => 'validlogin',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $this->assertNotEquals($sessionIdBefore, session()->getId());
    }

    public function test_logout_requires_post_and_invalidates_session()
    {
        $user = User::factory()->create();

        // GET /logout should fail (405 Method Not Allowed or 404)
        $getResponse = $this->actingAs($user)->get('/logout');
        $this->assertTrue(in_array($getResponse->status(), [404, 405]));

        // POST /logout should succeed
        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}

