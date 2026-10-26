<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\LocalGovernment;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Friendlier display names for countries whose ISO names are long or awkward.
     */
    private const NAME_OVERRIDES = [
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'RU' => 'Russia',
        'KR' => 'South Korea',
        'KP' => 'North Korea',
        'IR' => 'Iran',
        'SY' => 'Syria',
        'VN' => 'Vietnam',
        'LA' => 'Laos',
        'BO' => 'Bolivia',
        'VE' => 'Venezuela',
        'TZ' => 'Tanzania',
        'MD' => 'Moldova',
        'TW' => 'Taiwan',
        'CD' => 'DR Congo',
        'CG' => 'Congo',
        'CI' => "Côte d'Ivoire",
        'FM' => 'Micronesia',
        'PS' => 'Palestine',
        'BN' => 'Brunei',
        'CZ' => 'Czechia',
        'NL' => 'Netherlands',
        'MK' => 'North Macedonia',
        'VA' => 'Vatican City',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCountries();
            $this->seedNigeria();
        });
    }

    private function seedCountries(): void
    {
        $countries = json_decode(file_get_contents(database_path('data/countries.json')), true);
        $now = now();

        $rows = collect($countries)->map(fn ($c) => [
            'iso2' => $c['alpha-2'],
            'name' => self::NAME_OVERRIDES[$c['alpha-2']] ?? $c['name'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        Country::upsert($rows, ['iso2'], ['name', 'updated_at']);
    }

    private function seedNigeria(): void
    {
        $nigeria = Country::where('iso2', 'NG')->firstOrFail();
        $data = json_decode(file_get_contents(database_path('data/nigeria_states_lgas.json')), true);

        foreach ($data as $stateName => $lgas) {
            $state = State::firstOrCreate(['country_id' => $nigeria->id, 'name' => $stateName]);

            $existing = $state->localGovernments()->pluck('name')->all();
            $now = now();

            $missing = collect($lgas)
                ->diff($existing)
                ->map(fn ($name) => [
                    'country_id' => $nigeria->id,
                    'state_id' => $state->id,
                    'name' => $name,
                    'is_custom' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all();

            if ($missing) {
                LocalGovernment::insert($missing);
            }
        }
    }
}
