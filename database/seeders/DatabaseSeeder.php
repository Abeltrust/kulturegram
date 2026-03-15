<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CultureSeeder::class,
            LifeChapterSeeder::class,
            CommunitySeeder::class,
            PostSeeder::class,
            EventSeeder::class,
            CommentSeeder::class,
            LiveStreamSeeder::class,
            VaultItemSeeder::class,
            InteractionSeeder::class,
        ]);
    }
}
