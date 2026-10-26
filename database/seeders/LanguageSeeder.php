<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/languages.json')), true);
        $now = now();

        $rows = collect($data['nigerian'])
            ->merge($data['african'])
            ->merge($data['world'])
            ->unique(fn ($name) => mb_strtolower($name))
            ->map(fn ($name) => [
                'name' => $name,
                'is_custom' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all();

        Language::upsert($rows, ['name'], ['updated_at']);
    }
}
