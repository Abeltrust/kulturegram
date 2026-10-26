<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Language;
use App\Models\LocalGovernment;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Handles a user's place of origin (country / state / LGA or local area)
 * and spoken languages. Shared by registration and (later) profile editing.
 */
class UserOriginService
{
    public const OPTIONS_CACHE_KEY = 'user_origin.options';
    public const MAX_LANGUAGES = 10;

    /**
     * Validation rules. Every field is optional so users can skip this step.
     */
    public function rules(Request $request): array
    {
        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => [
                'nullable', 'integer',
                Rule::exists('states', 'id')->where('country_id', $request->input('country_id')),
            ],
            'local_government_id' => [
                'nullable', 'integer',
                Rule::exists('local_governments', 'id')->where('state_id', $request->input('state_id')),
            ],
            'local_area' => ['nullable', 'string', 'max:120'],
            'language_ids' => ['nullable', 'array', 'max:' . self::MAX_LANGUAGES],
            'language_ids.*' => ['integer', 'exists:languages,id'],
            'new_languages' => ['nullable', 'array', 'max:' . self::MAX_LANGUAGES],
            'new_languages.*' => ['string', 'min:2', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'state_id.exists' => 'Please choose a state from the selected country.',
            'local_government_id.exists' => 'Please choose a local government from the selected state.',
            'language_ids.max' => 'You can pick up to ' . self::MAX_LANGUAGES . ' languages.',
        ];
    }

    /**
     * Data for the registration dropdowns, cached since it rarely changes.
     */
    public function options(): array
    {
        return Cache::remember(self::OPTIONS_CACHE_KEY, now()->addDay(), function () {
            $countries = Country::orderBy('name')->get(['id', 'name', 'iso2'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'iso2' => $c->iso2, 'flag' => $c->flag])
                ->values();

            $statesByCountry = State::with(['localGovernments' => fn ($q) => $q->where('is_custom', false)->orderBy('name')])
                ->orderBy('name')
                ->get()
                ->groupBy('country_id')
                ->map(fn ($states) => $states->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'lgas' => $s->localGovernments->map(fn ($l) => ['id' => $l->id, 'name' => $l->name])->values(),
                ])->values());

            $languages = Language::orderBy('name')->get(['id', 'name']);

            $popularNames = json_decode(file_get_contents(database_path('data/languages.json')), true)['popular'] ?? [];
            $popular = $languages->whereIn('name', $popularNames)
                ->sortBy(fn ($l) => array_search($l->name, $popularNames))
                ->pluck('id')->values();

            return [
                'countries' => $countries,
                'statesByCountry' => $statesByCountry,
                'languages' => $languages->map(fn ($l) => ['id' => $l->id, 'name' => $l->name])->values(),
                'popularLanguageIds' => $popular,
            ];
        });
    }

    /**
     * User-added local areas for a country that has no official state/LGA data.
     */
    public function customAreas(Country $country, ?string $search = null)
    {
        return LocalGovernment::where('country_id', $country->id)
            ->whereNull('state_id')
            ->when($search, fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);
    }

    /**
     * Persist origin + languages for the user from validated input.
     */
    public function save(User $user, array $data): void
    {
        $country = !empty($data['country_id']) ? Country::find($data['country_id']) : null;
        $state = $country && !empty($data['state_id']) ? State::find($data['state_id']) : null;
        $lga = $state && !empty($data['local_government_id']) ? LocalGovernment::find($data['local_government_id']) : null;

        // Countries without official state data: reuse or create a user-added local area.
        if ($country && !$state && !$country->states()->exists() && filled($data['local_area'] ?? null)) {
            $lga = $this->findOrCreateCustomArea($country, $data['local_area'], $user);
        }

        $attributes = [
            'country_id' => $country?->id,
            'state_id' => $state?->id,
            'local_government_id' => $lga?->id,
        ];

        // Keep the existing free-text `location` field populated so current profile/timeline views show it.
        if ($country && blank($user->location)) {
            $attributes['location'] = collect([$lga?->name, $state?->name, $country->name])->filter()->implode(', ');
        }

        $languageIds = $this->resolveLanguageIds($data['language_ids'] ?? [], $data['new_languages'] ?? []);

        if ($languageIds) {
            $user->spokenLanguages()->sync($languageIds);
            // Mirror into the legacy JSON column used by the profile page.
            $attributes['languages'] = Language::whereIn('id', $languageIds)->orderBy('name')->pluck('name')->all();
        }

        $user->update($attributes);
    }

    private function findOrCreateCustomArea(Country $country, string $name, User $user): LocalGovernment
    {
        $name = $this->normalizeName($name);

        $existing = LocalGovernment::where('country_id', $country->id)
            ->whereNull('state_id')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        return $existing ?? LocalGovernment::create([
            'country_id' => $country->id,
            'state_id' => null,
            'name' => $name,
            'is_custom' => true,
            'created_by' => $user->id,
        ]);
    }

    private function resolveLanguageIds(array $ids, array $newNames): array
    {
        $ids = array_map('intval', $ids);
        $createdAny = false;

        foreach ($newNames as $name) {
            $name = $this->normalizeName($name);
            if ($name === '') {
                continue;
            }

            $language = Language::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

            if (!$language) {
                $language = Language::create(['name' => $name, 'is_custom' => true]);
                $createdAny = true;
            }

            $ids[] = $language->id;
        }

        if ($createdAny) {
            Cache::forget(self::OPTIONS_CACHE_KEY);
        }

        return array_slice(array_values(array_unique($ids)), 0, self::MAX_LANGUAGES);
    }

    /**
     * Trim, collapse whitespace, and title-case input typed entirely in lower case.
     */
    private function normalizeName(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)));

        return $value === mb_strtolower($value) ? Str::title($value) : $value;
    }
}
