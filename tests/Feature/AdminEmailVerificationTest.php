<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_verify_user_email_and_api_reflects_it(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $adminRole->permissions()->create(['name' => 'Manage Users', 'slug' => 'manage_users']);

        $admin = User::factory()->create(['status' => 'active']);
        $admin->roles()->attach($adminRole);

        $user = User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post("/admin/users/{$user->id}/verify-email")
            ->assertRedirect()
            ->assertSessionHas('success', 'pending@example.com has been verified.');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'user_email_verified',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);

        Sanctum::actingAs($user->fresh());

        $response = $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'pending@example.com')
            ->assertJsonPath('data.email_verified', true);

        $this->assertNotNull($response->json('data.email_verified_at'));
    }
}
