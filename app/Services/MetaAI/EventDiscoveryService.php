<?php

namespace App\Services\MetaAI;

use App\Models\CulturalRecommendationDismissal;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EventDiscoveryService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.spark', 'muse-spark-1.3');
    }

    /**
     * Recommend upcoming cultural events matching voluntary interests and languages.
     */
    public function recommendEvents(User $user, int $limit = 6): array
    {
        $prefs = (array) ($user->preferences ?? []);
        if (isset($prefs['ai_recommendations_enabled']) && $prefs['ai_recommendations_enabled'] === false) {
            return [
                'enabled' => false,
                'opted_out' => true,
                'recommendations' => [],
            ];
        }

        $attendingIds = $user->attendingEvents()->pluck('events.id')->toArray();
        $dismissedIds = CulturalRecommendationDismissal::where('user_id', $user->id)
            ->where('recommendation_type', 'event')
            ->pluck('target_id')
            ->toArray();

        $excludeIds = array_unique(array_merge($attendingIds, $dismissedIds, [$user->id]));

        $userInterests = array_map('strtolower', (array) ($user->cultural_interests ?? []));
        $userBackground = strtolower(trim((string) ($user->cultural_background ?? '')));

        $events = Event::with(['organizer', 'community'])
            ->where('event_date', '>=', now()->toDateString())
            ->where('organizer_id', '!=', $user->id)
            ->whereNotIn('id', $excludeIds)
            ->take(30)
            ->get();

        $scored = [];
        foreach ($events as $event) {
            $score = 15; // Base score for upcoming community gatherings
            $reasons = [];

            $title = strtolower($event->title);
            $desc = strtolower($event->description ?? '');
            $type = strtolower($event->type ?? '');
            $location = strtolower($event->location ?? '');

            // Cultural background match
            if (!empty($userBackground) && (str_contains($title, $userBackground) || str_contains($desc, $userBackground))) {
                $score += 35;
                $reasons[] = 'heritage connection (' . ucwords($user->cultural_background) . ')';
            }

            // Interest match
            foreach ($userInterests as $interest) {
                if (str_contains($title, $interest) || str_contains($desc, $interest) || str_contains($type, $interest)) {
                    $score += 25;
                    $reasons[] = "an interest in {$interest}";
                    break;
                }
            }

            // Location/Online match
            if ($event->is_online) {
                $score += 10;
            } elseif (!empty($user->location) && str_contains($location, strtolower($user->location))) {
                $score += 20;
                $reasons[] = 'a nearby location';
            }

            $explanation = !empty($reasons)
                ? 'Recommended because you share ' . implode(' and ', $reasons) . '.'
                : 'Upcoming cultural gathering in the community.';

            $scored[] = [
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->event_date->format('M d, Y'),
                    'event_time' => $event->event_time,
                    'location' => $event->location,
                    'type' => $event->type,
                    'is_online' => (bool) $event->is_online,
                    'price' => (float) $event->price,
                    'image' => $event->image ? asset('storage/' . $event->image) : null,
                    'organizer_name' => $event->organizer->name ?? 'Community Elder',
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
     * AI Cultural Reach Optimizer for Event Organizers:
     * Suggests improved title, engaging cultural description, target audiences, and reach tags.
     */
    public function optimizeEvent(array $eventData): array
    {
        $rawTitle = trim((string) ($eventData['title'] ?? ''));
        $rawDesc = trim((string) ($eventData['description'] ?? ''));
        $category = trim((string) ($eventData['type'] ?? 'Cultural Gathering'));

        if (empty($rawTitle) && empty($rawDesc)) {
            return [
                'success' => false,
                'error' => 'Please provide at least an event title or description to optimize.',
                'suggestions' => null,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => true,
                'configured' => false,
                'suggestions' => [
                    'optimized_title' => $rawTitle,
                    'optimized_description' => $rawDesc,
                    'suggested_tags' => ['cultural-heritage', 'indigenous-traditions', 'community-gathering'],
                    'reach_tags' => ['cultural-heritage', 'indigenous-traditions', 'community-gathering'],
                    'target_communities' => ['Preservers & Historians', 'Youth & Learners'],
                    'reach_tips' => 'Add specific language or clan details to help relevant community members discover this event.',
                    'timing_suggestions' => 'Add specific language or clan details to help relevant community members discover this event.',
                ],
            ];
        }

        $systemPrompt = <<<PROMPT
You are Kulturegram's Cultural Event Discovery Specialist.
Your mission is to help organizers articulate the cultural richness and accessibility of their gatherings without exaggeration.

Return response EXCLUSIVELY in valid JSON matching this schema:
{
  "optimized_title": "Clear, culturally resonant title",
  "optimized_description": "2-3 sentence engaging description explaining cultural value and welcome note",
  "suggested_tags": ["tag1", "tag2", "tag3"],
  "target_communities": ["Audience or tradition group 1", "Audience group 2"],
  "reach_tips": "Practical tip on dates or regional coordination"
}
PROMPT;

        $userPrompt = "Event Title: {$rawTitle}\nCategory: {$category}\nCurrent Description: {$rawDesc}\n\nPlease generate optimization recommendations.";

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 1024,
        ];

        $res = $this->client->postJson('chat/completions', $payload, $this->model);
        if (!$res['success']) {
            Log::info('[EventDiscoveryService] Meta AI call unavailable, falling back to heuristic suggestions.');
            return [
                'success' => true,
                'configured' => true,
                'fallback' => true,
                'suggestions' => [
                    'optimized_title' => !empty($rawTitle) ? ($rawTitle . ' — Cultural Gathering & Celebration') : 'Cultural Gathering & Celebration',
                    'optimized_description' => !empty($rawDesc) ? ($rawDesc . ' Join community members and elders for an authentic cultural experience.') : 'Join community members and elders for an authentic cultural celebration.',
                    'suggested_tags' => ['cultural-heritage', 'indigenous-traditions', 'community-gathering'],
                    'reach_tags' => ['cultural-heritage', 'indigenous-traditions', 'community-gathering'],
                    'target_communities' => ['Cultural Preservers & Historians', 'Youth & Community Learners'],
                    'reach_tips' => 'Schedule during weekend afternoons to maximize community attendance.',
                    'timing_suggestions' => 'Schedule during weekend afternoons to maximize community attendance.',
                ],
            ];
        }

        $raw = $this->extractContent($res['data']);
        $clean = trim($raw);

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{[\s\S]*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            return [
                'success' => true,
                'configured' => true,
                'fallback' => true,
                'suggestions' => [
                    'optimized_title' => $rawTitle,
                    'optimized_description' => $rawDesc,
                    'suggested_tags' => ['cultural-heritage', 'community-gathering'],
                    'reach_tags' => ['cultural-heritage', 'community-gathering'],
                    'target_communities' => ['Community Members'],
                    'reach_tips' => 'Share with local cultural groups for maximum reach.',
                    'timing_suggestions' => 'Share with local cultural groups for maximum reach.',
                ],
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'suggestions' => [
                'optimized_title' => strip_tags(trim((string) ($decoded['optimized_title'] ?? $rawTitle))),
                'optimized_description' => strip_tags(trim((string) ($decoded['optimized_description'] ?? $rawDesc))),
                'suggested_tags' => array_map('strip_tags', (array) ($decoded['suggested_tags'] ?? [])),
                'reach_tags' => array_map('strip_tags', (array) ($decoded['suggested_tags'] ?? [])),
                'target_communities' => array_map('strip_tags', (array) ($decoded['target_communities'] ?? [])),
                'reach_tips' => strip_tags(trim((string) ($decoded['reach_tips'] ?? ''))),
                'timing_suggestions' => strip_tags(trim((string) ($decoded['reach_tips'] ?? ''))),
            ],
        ];
    }

    protected function extractContent(array $data): string
    {
        if (!empty($data['choices'][0]['message']['content'])) {
            return (string) $data['choices'][0]['message']['content'];
        }
        if (!empty($data['choices'][0]['message']['reasoning_content'])) {
            return (string) $data['choices'][0]['message']['reasoning_content'];
        }
        return '';
    }
}
