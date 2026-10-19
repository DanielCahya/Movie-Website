<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_normal_user_cannot_update_other_users()
    {
        $attacker = User::factory()->create(['role' => 'user']);
        $victim = User::factory()->create(['role' => 'superadmin', 'email' => 'victim@example.com']);

        $response = $this->actingAs($attacker)->post("/updated-data/{$victim->id}", [
            'username' => 'hackedadmin',
            'email' => 'hacked@example.com',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'email' => 'victim@example.com',
        ]);
    }

    public function test_normal_user_cannot_delete_users()
    {
        $attacker = User::factory()->create(['role' => 'user']);
        $victim = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($attacker)->delete("/delete-user/{$victim->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $victim->id]);
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertOk();
    }

    public function test_admin_can_update_regular_user()
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->post("/updated-data/{$targetUser->id}", [
            'username' => 'updateduser',
            'email' => 'updateduser@example.com',
        ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'username' => 'updateduser',
            'email' => 'updateduser@example.com',
        ]);
    }

    public function test_admin_cannot_update_superadmin()
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create(['email' => 'super@example.com']);

        $response = $this->actingAs($admin)->post("/updated-data/{$superadmin->id}", [
            'username' => 'compromised',
            'email' => 'compromised@example.com',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
            'email' => 'super@example.com',
        ]);
    }

    public function test_admin_cannot_delete_superadmin()
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($admin)->delete("/delete-user/{$superadmin->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_admin_cannot_delete_another_admin()
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $response = $this->actingAs($admin1)->delete("/delete-user/{$admin2->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin2->id]);
    }

    public function test_admin_cannot_delete_self()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/delete-user/{$admin->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_regular_user()
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->delete("/delete-user/{$targetUser->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_superadmin_cannot_delete_self()
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->delete("/delete-user/{$superadmin->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }
}

