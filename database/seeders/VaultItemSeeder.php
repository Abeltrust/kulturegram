<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaultItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            \App\Models\VaultItem::factory()->count(rand(1, 3))->create([
                'user_id' => $user->id
            ]);
        }
    }
}
