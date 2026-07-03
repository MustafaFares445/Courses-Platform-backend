<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_non_admin_users_only(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonPath('data.0.id', $student->id)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_deactivate_student_and_student_can_not_login(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'password',
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$student->id}/status", [
            'isActive' => false,
        ])->assertOk()
            ->assertJsonPath('data.isActive', false);

        $this->postJson('/api/auth/login', [
            'email' => 'student@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_super_admin_can_create_and_update_admin_user_password(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/admin/admin-users', [
            'name' => 'Dashboard Admin',
            'email' => 'dashboard-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'userType' => User::TYPE_ADMIN,
        ])->assertCreated();

        $adminId = $response->json('data.id');

        $this->patchJson("/api/admin/admin-users/{$adminId}", [
            'name' => 'Updated Dashboard Admin',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Dashboard Admin');
    }

    public function test_regular_admin_can_not_create_admin_user(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/admin-users', [
            'name' => 'Another Admin',
            'email' => 'another-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'userType' => User::TYPE_ADMIN,
        ])->assertForbidden();
    }
}
