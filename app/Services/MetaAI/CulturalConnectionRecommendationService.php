<?php

namespace App\Services\MetaAI;

use App\Models\Community;
use App\Models\CulturalRecommendationDismissal;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CulturalConnectionRecommendationService
{
    /**
     * Recommend like-minded cultural peers based on voluntarily shared interests,
     * languages, and heritage goals.
     */
    public function recommendUsers(User $user, int $limit = 5): array
    {
        // Check user privacy opt-out
        if ($this->hasOptedOut($user)) {
            return [
                'enabled' => false,
                'opted_out' => true,
                'recommendations' => [],
                'message' => 'You have opted out of AI-powered personalized recommendations in your Privacy Settings.',
            ];
        }

        // Get dismissed user IDs
        $dismissedIds = CulturalRecommendationDismissal::where('user_id', $user->id)
            ->where('recommendation_type', 'user')
            ->pluck('target_id')
            ->toArray();

        $dismissedIds[] = $user->id; // Exclude self

        // User voluntary interests and languages
        $userInterests = (array) ($user->cultural_interests ?? []);
        $userLanguages = (array) ($user->languages ?? []);
        $userBackground = strtolower(trim((string) ($user->cultural_background ?? '')));

        // Query active candidates
        $candidates = User::whereNotIn('id', $dismissedIds)
            ->where(function ($q) use ($userBackground, $userInterests) {
                if (!empty($userBackground)) {
                    $q->where('cultural_background', 'like', "%{$userBackground}%");
                }
                if ($userInterests) {
                    $q->orWhereNotNull('cultural_interests');
                }
            })
            ->take(30)
            ->get();

        $scored = [];
        foreach ($candidates as $candidate) {
            $candInterests = (array) ($candidate->cultural_interests ?? []);
            $candLanguages = (array) ($candidate->languages ?? []);
            $candBackground = strtolower(trim((string) ($candidate->cultural_background ?? '')));

            $score = 0;
            $reasons = [];

            // 1. Shared cultural background
            if (!empty($userBackground) && !empty($candBackground) && ($userBackground === $candBackground || str_contains($candBackground, $userBackground))) {
                $score += 35;
                $reasons[] = 'shared cultural heritage (' . ucwords($candidate->cultural_background) . ')';
            }

            // 2. Shared cultural interests
            $commonInterests = array_intersect(
                array_map('strtolower', $userInterests),
                array_map('strtolower', $candInterests)
            );
            if (!empty($commonInterests)) {
                $score += min(30, count($commonInterests) * 15);
                $reasons[] = 'common passion for ' . implode(', ', array_slice($commonInterests, 0, 2));
            }

            // 3. Shared languages
            $commonLangs = array_intersect(
                array_map('strtolower', $userLanguages),
                array_map('strtolower', $candLanguages)
            );
            if (!empty($commonLangs)) {
                $score += 20;
                $reasons[] = 'shared language (' . implode(', ', array_slice($commonLangs, 0, 2)) . ')';
            }

            // 4. Same geographical region/state
            if (!empty($user->state_id) && !empty($candidate->state_id) && $user->state_id === $candidate->state_id) {
                $score += 15;
                $reasons[] = 'same region (' . ($candidate->state->name ?? 'Regional') . ')';
            }

            if ($score > 0) {
                $explanation = 'Recommended because you share ' . implode(' and ', $reasons) . '.';
                $scored[] = [
                    'user' => [
                        'id' => $candidate->id,
                        'name' => $candidate->name,
                        'username' => $candidate->username ?? 'user',
                        'avatar' => $candidate->profile_photo_url ?? null,
                        'cultural_background' => $candidate->cultural_background,
                        'location' => $candidate->location,
                        'bio' => $candidate->bio,
                    ],
                    'match_score' => $score,
                    'explanation' => $explanation,
                ];
            }
        }

        // Sort by highest match score
        usort($scored, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        return [
            'enabled' => true,
            'opted_out' => false,
            'recommendations' => array_slice($scored, 0, $limit),
            'message' => null,
        ];
    }

    /**
     * Recommend cultural communities aligning with declared cultural interests.
     */
    public function recommendCommunities(User $user, int $limit = 4): array
    {
        if ($this->hasOptedOut($user)) {
            return ['enabled' => false, 'opted_out' => true, 'recommendations' => []];
        }

        $joinedIds = $user->communities()->pluck('communities.id')->toArray();
        $dismissedIds = CulturalRecommendationDismissal::where('user_id', $user->id)
            ->where('recommendation_type', 'community')
            ->pluck('target_id')
            ->toArray();

        $excludeIds = array_merge($joinedIds, $dismissedIds);

        $userInterests = array_map('strtolower', (array) ($user->cultural_interests ?? []));
        $userBackground = strtolower(trim((string) ($user->cultural_background ?? '')));

        $communities = Community::whereNotIn('id', $excludeIds)->take(20)->get();

        $scored = [];
        foreach ($communities as $community) {
            $score = 10; // Base score for open cultural communities
            $reasons = [];

            $cName = strtolower($community->name);
            $cDesc = strtolower($community->description ?? '');

            if (!empty($userBackground) && (str_contains($cName, $userBackground) || str_contains($cDesc, $userBackground))) {
                $score += 40;
                $reasons[] = 'heritage focus (' . ucwords($user->cultural_background) . ')';
            }

            foreach ($userInterests as $interest) {
                if (str_contains($cName, $interest) || str_contains($cDesc, $interest)) {
                    $score += 25;
                    $reasons[] = "interest in {$interest}";
                    break;
                }
            }

            $explanation = !empty($reasons)
                ? 'Recommended because this circle aligns with your ' . implode(' and ', $reasons) . '.'
                : 'Popular cultural circle with active preservers.';

            $scored[] = [
                'community' => [
                    'id' => $community->id,
                    'name' => $community->name,
                    'description' => $community->description,
                    'image' => $community->image ? asset('storage/' . $community->image) : null,
                    'members_count' => $community->members()->count(),
                ],
                'match_score' => $score,
                'explanation' => $explanation,
            ];
        }

        usort($scored, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        return [
            'enabled' => true,
            'opted_out' => false,
            'recommendations' => array_slice($scored, 0, $limit),
        ];
    }

    /**
     * Record voluntary dismissal of a recommendation.
     */
    public function dismiss(User $user, string $type, int $targetId): bool
    {
        if (!in_array($type, ['user', 'community', 'event'], true)) {
            return false;
        }

        CulturalRecommendationDismissal::firstOrCreate([
            'user_id' => $user->id,
            'recommendation_type' => $type,
            'target_id' => $targetId,
        ]);

        return true;
    }

    /**
     * Check if user has opted out of AI recommendations in settings.
     */
    public function isOptedOut(User $user): bool
    {
        return $this->hasOptedOut($user);
    }

    /**
     * Toggle user's voluntary opt-in/opt-out status for AI recommendations.
     */
    public function toggleOptIn(User $user, bool $enabled): bool
    {
        $prefs = (array) ($user->preferences ?? []);
        $prefs['ai_recommendations_enabled'] = $enabled;
        $user->preferences = $prefs;
        $user->save();
        return $enabled;
    }

    protected function hasOptedOut(User $user): bool
    {
        $prefs = (array) ($user->preferences ?? []);
        return isset($prefs['ai_recommendations_enabled']) && $prefs['ai_recommendations_enabled'] === false;
    }
}
