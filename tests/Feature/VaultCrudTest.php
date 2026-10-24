<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VaultCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_vault_index_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('vault.index'));

        $response->assertStatus(200);
        $response->assertSee('Personal Vault');
    }

    public function test_user_can_upload_item_to_vault(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('ancestral_heirloom.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('vault.store'), [
            'title' => 'Ancient Ivory Comb',
            'description' => 'Heirloom passed down through 5 generations.',
            'type' => 'photos',
            'access_level' => 'private',
            'cultural_significance' => 'Traditional wedding ornamentation',
            'files' => [$file],
        ]);

        $response->assertRedirect(route('vault.index'));
        $this->assertDatabaseHas('vault_items', [
            'user_id' => $user->id,
            'title' => 'Ancient Ivory Comb',
            'type' => 'photos',
            'access_level' => 'private',
        ]);
    }

    public function test_user_can_view_own_vault_item(): void
    {
        $user = User::factory()->create();
        $vaultItem = VaultItem::factory()->create([
            'user_id' => $user->id,
            'access_level' => 'private',
        ]);

        $response = $this->actingAs($user)->get(route('vault.show', $vaultItem));

        $response->assertStatus(200);
        $response->assertSee($vaultItem->title);
    }

    public function test_other_user_cannot_view_or_update_private_vault_item(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);
        $vaultItem = VaultItem::factory()->create([
            'user_id' => $owner->id,
            'access_level' => 'private',
        ]);

        $viewResponse = $this->actingAs($stranger)->get(route('vault.show', $vaultItem));
        $viewResponse->assertStatus(403);

        $updateResponse = $this->actingAs($stranger)->put(route('vault.update', $vaultItem), [
            'title' => 'Stolen Title',
            'access_level' => 'private',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($stranger)->delete(route('vault.destroy', $vaultItem));
        $deleteResponse->assertStatus(403);
    }

    public function test_user_can_update_own_vault_item(): void
    {
        $user = User::factory()->create();
        $vaultItem = VaultItem::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Name',
            'access_level' => 'private',
        ]);

        $response = $this->actingAs($user)->put(route('vault.update', $vaultItem), [
            'title' => 'Preserved Name Updated',
            'description' => 'Updated detailed provenance.',
            'access_level' => 'family',
            'cultural_significance' => 'Significance note',
        ]);

        $response->assertRedirect(route('vault.show', $vaultItem));
        $this->assertDatabaseHas('vault_items', [
            'id' => $vaultItem->id,
            'title' => 'Preserved Name Updated',
            'access_level' => 'family',
        ]);
    }

    public function test_user_can_delete_own_vault_item(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $vaultItem = VaultItem::factory()->create([
            'user_id' => $user->id,
            'file_path' => 'vault/' . $user->id . '/sample.jpg',
        ]);

        Storage::disk('private')->put($vaultItem->file_path, 'dummy content');

        $response = $this->actingAs($user)->delete(route('vault.destroy', $vaultItem));

        $response->assertRedirect(route('vault.index'));
        $this->assertDatabaseMissing('vault_items', ['id' => $vaultItem->id]);
    }
}
