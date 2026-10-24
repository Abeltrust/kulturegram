<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VaultItem;

class VaultItemPolicy
{
    /**
     * Determine whether the user can view the vault item.
     */
    public function view(User $user, VaultItem $vaultItem): bool
    {
        return (int) $user->id === (int) $vaultItem->user_id || $vaultItem->access_level === 'public' || (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can update the vault item.
     */
    public function update(User $user, VaultItem $vaultItem): bool
    {
        return (int) $user->id === (int) $vaultItem->user_id || (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can delete the vault item.
     */
    public function delete(User $user, VaultItem $vaultItem): bool
    {
        return (int) $user->id === (int) $vaultItem->user_id || (bool) $user->is_admin;
    }
}
