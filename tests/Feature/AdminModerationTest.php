<?php

namespace Tests\Feature;

use App\Models\Culture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_moderation_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/cultures');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_moderation_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/cultures');

        $response->assertStatus(200);
        $response->assertSee('Admin Moderation');
    }

    public function test_admin_can_approve_pending_culture(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $culture = Culture::factory()->create(['status' => 'pending_review']);

        $response = $this->actingAs($admin)->post(route('admin.cultures.approve', $culture));

        $response->assertRedirect();
        $this->assertDatabaseHas('cultures', [
            'id' => $culture->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_feature_and_unfeature_culture(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $culture = Culture::factory()->create(['status' => 'approved']);

        // Feature
        $response = $this->actingAs($admin)->post(route('admin.cultures.feature', $culture));
        $response->assertRedirect();
        $this->assertDatabaseHas('cultures', [
            'id' => $culture->id,
            'status' => 'featured',
        ]);

        // Unfeature back to approved
        $response = $this->actingAs($admin)->post(route('admin.cultures.feature', $culture));
        $response->assertRedirect();
        $this->assertDatabaseHas('cultures', [
            'id' => $culture->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_or_delete_culture(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $culture = Culture::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.cultures.destroy', $culture));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cultures', [
            'id' => $culture->id,
        ]);
    }
}
