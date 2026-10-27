<?php

namespace App\Services\MetaAI;

use App\Models\Community;
use App\Models\Culture;
use App\Models\Event;
use App\Models\User;
use App\Models\CulturalRecommendationDismissal;
use Illuminate\Support\Collection;

class CulturalOpportunityService
{
    /**
     * Generate culturally grounded opportunity recommendations for a user.
     *
     * @param User $user
     * @return array
     */
    public function getOpportunities(User $user): array
    {
        // 1. Check opt-out preference
        $prefs = $user->preferences ?? [];
        if (isset($prefs['opt_in_recommendations']) && !$prefs['opt_in_recommendations']) {
            return [
                'opted_out' => true,
                'message' => 'Cultural opportunity recommendations are disabled in your preferences.',
                'opportunities' => [],
            ];
        }

        // 2. Fetch user's active dismissals
        $dismissals = CulturalRecommendationDismissal::where('user_id', $user->id)
            ->get()
            ->groupBy('recommendation_type');

        $dismissedCultureIds = $dismissals->get('culture', collect())->pluck('target_id')->toArray();
        $dismissedCommunityIds = $dismissals->get('community', collect())->pluck('target_id')->toArray();
        $dismissedEventIds = $dismissals->get('event', collect())->pluck('target_id')->toArray();
        $dismissedUserIds = $dismissals->get('user', collect())->pluck('target_id')->toArray();

        // 3. User cultural profile
        $interests = array_filter((array) ($user->cultural_interests ?? []));
        $languages = array_filter((array) ($user->languages ?? []));
        $background = $user->cultural_background;

        $opportunities = [];

        // Category A: Heritage Preservation Initiatives (Cultures needing custodian enrichment)
        $preservationProjects = $this->findPreservationProjects($interests, $languages, $background, $dismissedCultureIds);
        foreach ($preservationProjects as $proj) {
            $opportunities[] = $proj;
        }

        // Category B: Language & Cultural Communities
        $communities = $this->findCommunities($interests, $languages, $background, $user, $dismissedCommunityIds);
        foreach ($communities as $comm) {
            $opportunities[] = $comm;
        }

        // Category C: Cultural Workshops & Events
        $events = $this->findEvents($interests, $user, $dismissedEventIds);
        foreach ($events as $evt) {
            $opportunities[] = $evt;
        }

        // Category D: Opt-In Collaborators
        $collaborators = $this->findCollaborators($user, $interests, $languages, $background, $dismissedUserIds);
        foreach ($collaborators as $collab) {
            $opportunities[] = $collab;
        }

        return [
            'opted_out' => false,
            'user_interests' => $interests,
            'user_languages' => $languages,
            'opt_in_collaboration' => (bool) $user->opt_in_collaboration,
            'total_opportunities' => count($opportunities),
            'opportunities' => $opportunities,
        ];
    }

    /**
     * Find cultural preservation projects and endangered traditions needing documentation.
     */
    protected function findPreservationProjects(array $interests, array $languages, ?string $background, array $dismissedIds): array
    {
        $query = Culture::where('status', 'approved')
            ->whereNotIn('id', $dismissedIds);

        // Target cultures with high endangerment or missing rich audio/video media
        $query->where(function ($q) use ($interests, $languages, $background) {
            $hasFilter = false;
            if (!empty($interests)) {
                $q->where(function ($sub) use ($interests) {
                    foreach ($interests as $interest) {
                        $sub->orWhere('name', 'like', "%{$interest}%")
                            ->orWhere('category', 'like', "%{$interest}%");
                    }
                });
                $hasFilter = true;
            }

            if (!empty($languages)) {
                $q->orWhereIn('language', $languages);
                $hasFilter = true;
            }

            if ($background) {
                $q->orWhere('region', 'like', "%{$background}%")
                  ->orWhere('name', 'like', "%{$background}%");
                $hasFilter = true;
            }

            if (!$hasFilter) {
                $q->whereNotNull('endangerment_level');
            }
        });

        $results = $query->limit(3)->get();
        $items = [];

        foreach ($results as $culture) {
            $items[] = [
                'type' => 'preservation_project',
                'target_id' => $culture->id,
                'title' => $culture->name,
                'subtitle' => "Heritage Documentation • {$culture->category}",
                'region' => $culture->region,
                'language' => $culture->language,
                'badge' => $culture->endangerment_level ? ucfirst(str_replace('_', ' ', $culture->endangerment_level)) : 'Documentation Initiative',
                'url' => route('cultural-hub.show', $culture->id),
                'explanation' => "Recommended because this {$culture->category} initiative relates to your declared cultural background and traditions.",
            ];
        }

        return $items;
    }

    /**
     * Find language-learning and cultural practice communities.
     */
    protected function findCommunities(array $interests, array $languages, ?string $background, User $user, array $dismissedIds): array
    {
        $userCommunityIds = $user->communities()->pluck('communities.id')->toArray();
        $excluded = array_merge($userCommunityIds, $dismissedIds);

        $query = Community::whereNotIn('id', $excluded);

        if (!empty($interests) || !empty($languages) || $background) {
            $query->where(function ($q) use ($interests, $languages, $background) {
                foreach ($interests as $item) {
                    $q->orWhere('name', 'like', "%{$item}%")
                      ->orWhere('description', 'like', "%{$item}%");
                }
                foreach ($languages as $lang) {
                    $q->orWhere('name', 'like', "%{$lang}%")
                      ->orWhere('description', 'like', "%{$lang}%");
                }
                if ($background) {
                    $q->orWhere('name', 'like', "%{$background}%")
                      ->orWhere('description', 'like', "%{$background}%");
                }
            });
        }

        $results = $query->limit(3)->get();
        $items = [];

        foreach ($results as $community) {
            $items[] = [
                'type' => 'community',
                'target_id' => $community->id,
                'title' => $community->name,
                'subtitle' => 'Cultural Practice & Language Circle',
                'members_count' => $community->members()->count(),
                'url' => route('communities.show', $community->id),
                'explanation' => "Recommended community circle matching your language and cultural heritage interests.",
            ];
        }

        return $items;
    }

    /**
     * Find relevant cultural workshops, gatherings, and events.
     */
    protected function findEvents(array $interests, User $user, array $dismissedIds): array
    {
        $query = Event::where('event_date', '>=', now()->toDateString())
            ->whereNotIn('id', $dismissedIds);

        if (!empty($interests)) {
            $query->where(function ($q) use ($interests) {
                foreach ($interests as $item) {
                    $q->orWhere('title', 'like', "%{$item}%")
                      ->orWhere('description', 'like', "%{$item}%");
                }
            });
        }

        $results = $query->orderBy('event_date', 'asc')->limit(3)->get();
        $items = [];

        foreach ($results as $event) {
            $dateStr = $event->event_date ? $event->event_date->format('M j, Y') : 'Upcoming';
            $items[] = [
                'type' => 'event',
                'target_id' => $event->id,
                'title' => $event->title,
                'subtitle' => "Cultural Gathering • {$dateStr}",
                'location' => $event->location ?? 'Virtual / Kulturegram Meet',
                'url' => route('events.show', $event->id),
                'explanation' => "Recommended event aligned with your cultural interests and community focus.",
            ];
        }

        return $items;
    }

    /**
     * Find potential collaborators who have opted into collaboration.
     */
    protected function findCollaborators(User $currentUser, array $interests, array $languages, ?string $background, array $dismissedIds): array
    {
        // Strictly only suggest users who have explicitly set opt_in_collaboration = true
        $query = User::where('opt_in_collaboration', true)
            ->where('id', '!=', $currentUser->id)
            ->whereNotIn('id', $dismissedIds);

        if (!empty($interests) || !empty($languages) || $background) {
            $query->where(function ($q) use ($interests, $languages, $background) {
                if ($background) {
                    $q->orWhere('cultural_background', 'like', "%{$background}%");
                }
                foreach ($languages as $lang) {
                    $q->orWhereJsonContains('languages', $lang);
                }
                foreach ($interests as $interest) {
                    $q->orWhereJsonContains('cultural_interests', $interest);
                }
            });
        }

        $results = $query->limit(3)->get(['id', 'name', 'username', 'avatar', 'cultural_background', 'cultural_interests']);
        $items = [];

        foreach ($results as $collaborator) {
            $shared = array_intersect(
                (array) ($collaborator->cultural_interests ?? []),
                $interests
            );
            $sharedLabel = !empty($shared) ? implode(', ', array_slice($shared, 0, 2)) : ($collaborator->cultural_background ?? 'Heritage Preservation');

            $items[] = [
                'type' => 'user',
                'target_id' => $collaborator->id,
                'title' => $collaborator->name,
                'subtitle' => "Contributor • {$collaborator->cultural_background}",
                'avatar' => $collaborator->avatar,
                'url' => route('profile.user', $collaborator->id),
                'explanation' => "Opted-in collaborator working on {$sharedLabel} documentation and community research.",
            ];
        }

        return $items;
    }

    /**
     * Dismiss an opportunity so it won't be recommended again.
     */
    public function dismissOpportunity(User $user, string $type, int $targetId): bool
    {
        CulturalRecommendationDismissal::firstOrCreate([
            'user_id' => $user->id,
            'recommendation_type' => $type,
            'target_id' => $targetId,
        ]);

        return true;
    }

    /**
     * Update user's cultural interests and collaboration opt-in preferences.
     */
    public function updatePreferences(User $user, array $interests, bool $optInCollaboration, ?bool $optInRecommendations = null): bool
    {
        $prefs = $user->preferences ?? [];
        if ($optInRecommendations !== null) {
            $prefs['opt_in_recommendations'] = $optInRecommendations;
        }

        $cleanInterests = array_values(array_unique(array_filter(array_map('trim', $interests))));

        return $user->update([
            'cultural_interests' => $cleanInterests,
            'opt_in_collaboration' => $optInCollaboration,
            'preferences' => $prefs,
        ]);
    }
}
