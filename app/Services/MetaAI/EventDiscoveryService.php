<?php

namespace App\Services\MetaAI;

use App\Models\CulturalRecommendationDismissal;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    /**
     * AI-Assisted Cultural Description & Concept Generator
     * Takes raw key points or draft concept and generates a captivating, respectful cultural presentation.
     */
    public function suggestDescription(array $input): array
    {
        $rawPoints = trim((string) ($input['key_points'] ?? $input['description'] ?? ''));
        $title = trim((string) ($input['title'] ?? 'Cultural Gathering'));
        $type = trim((string) ($input['type'] ?? 'workshop'));
        $culturalFocus = trim((string) ($input['cultural_focus'] ?? ''));

        if (empty($rawPoints) && empty($title)) {
            return [
                'success' => false,
                'error' => 'Please provide key points or an event title to suggest a cultural description.',
            ];
        }

        if (!$this->client->isConfigured()) {
            // Intelligent heuristic fallback
            $concept = !empty($rawPoints) 
                ? "An immersive gathering exploring " . Str::limit($rawPoints, 120) 
                : "An authentic cultural gathering celebrating living heritage and community traditions.";
            
            $formatted = "Join us for {$title}, a dedicated {$type} centered around cultural heritage and community learning.\n\n"
                       . (!empty($rawPoints) ? "Key Highlights:\n• " . str_replace(["\n", "\r"], "\n• ", $rawPoints) . "\n\n" : "")
                       . "Attendees will gain firsthand insights into living traditions, engage directly with custodians, and participate in preserving ancestral knowledge for future generations. All community members, youth, and heritage enthusiasts are warmly welcome.";

            return [
                'success' => true,
                'configured' => false,
                'concept_summary' => $concept,
                'suggested_description' => $formatted,
                'tagline' => "Honoring our ancestors, empowering our community.",
            ];
        }

        $systemPrompt = <<<PROMPT
You are Kulturegram's Master Cultural Curator and Heritage Planner.
The user will provide rough key points or an initial brief about a planned cultural event or gathering.
Transform these key points into an engaging, culturally grounded, and captivating presentation.

Return response EXCLUSIVELY in valid JSON matching this schema:
{
  "concept_summary": "1-2 sentence core concept and thematic focus of this gathering",
  "suggested_description": "Full 2-3 paragraph presentation: introductory hook, specific cultural activities/knowledge shared, and an authentic welcoming call to the community.",
  "tagline": "Short evocative cultural tagline"
}
PROMPT;

        $userPrompt = "Event Title: {$title}\nType: {$type}\nCultural Focus: {$culturalFocus}\nKey Points / Draft Notes:\n{$rawPoints}";

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 1200,
        ];

        $res = $this->client->postJson('chat/completions', $payload, $this->model);
        if (!$res['success']) {
            $formatted = "Join us for {$title}, an interactive {$type} focusing on authentic community traditions.\n\n"
                       . "During this gathering, participants will explore: {$rawPoints}.\n\n"
                       . "This session provides a space to connect with elders, custodians, and peers passionate about living cultural preservation. All are welcome to attend, listen, and share.";

            return [
                'success' => true,
                'configured' => true,
                'fallback' => true,
                'concept_summary' => "Cultural gathering celebrating community heritage and ancestral practices.",
                'suggested_description' => $formatted,
                'tagline' => "Preserving traditions through community connection.",
            ];
        }

        $raw = $this->extractContent($res['data']);
        $clean = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{[\s\S]*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $formattedFallback = "Join us for {$title}, an interactive {$type} focusing on authentic community traditions.\n\n"
                           . (!empty($rawPoints) ? "During this gathering, participants will explore: {$rawPoints}.\n\n" : "")
                           . "This session provides a space to connect with elders, custodians, and peers passionate about living cultural preservation. All are welcome to attend, listen, and share.";

        $decoded = json_decode($clean, true);
        if (!is_array($decoded) || empty($decoded['suggested_description'])) {
            return [
                'success' => true,
                'configured' => true,
                'concept_summary' => "Cultural gathering celebrating community heritage.",
                'suggested_description' => !empty($clean) ? $clean : $formattedFallback,
                'tagline' => "Living heritage, shared together.",
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'concept_summary' => strip_tags(trim((string) ($decoded['concept_summary'] ?? ''))),
            'suggested_description' => strip_tags(trim((string) ($decoded['suggested_description'] ?? $formattedFallback))),
            'tagline' => strip_tags(trim((string) ($decoded['tagline'] ?? ''))),
        ];
    }

    /**
     * AI-Assisted Live Stream Caption & Title Generator
     */
    public function suggestLiveCaption(array $input): array
    {
        $rawNotes = trim((string) ($input['description'] ?? $input['key_points'] ?? $input['topic'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $category = trim((string) ($input['category'] ?? 'Heritage'));

        if (empty($rawNotes) && empty($title)) {
            return [
                'success' => false,
                'error' => 'Please provide stream notes, a topic, or an initial title.',
            ];
        }

        if (!$this->client->isConfigured()) {
            $suggestedTitle = !empty($title) ? $title : "Live Cultural Broadcast: " . Str::limit($rawNotes, 40);
            $caption = (!empty($rawNotes) ? $rawNotes . "\n\n" : "") . "🔴 Streaming live on Kulturegram! Join the broadcast to ask questions in real time, connect with elders and community members, and learn about living African traditions.";

            return [
                'success' => true,
                'configured' => false,
                'suggested_title' => $suggestedTitle,
                'caption' => $caption,
                'suggested_category' => !empty($category) ? $category : 'Oral Traditions',
            ];
        }

        $systemPrompt = <<<PROMPT
You are Kulturegram's Live Broadcast Producer.
Given an organizer's rough notes or topic about a live stream broadcast, generate:
1. A captivating, broadcast-ready cultural stream title.
2. An engaging live stream caption with a strong hook, viewer discussion prompt, and community call-to-action.
3. Best category (e.g., Oral Traditions, Traditional Music, Living Crafts, Language Circle, Cultural Ritual).

Return response EXCLUSIVELY in valid JSON matching this schema:
{
  "suggested_title": "Short, catchy, broadcast-ready title",
  "caption": "Captivating 1-2 paragraph description with live viewer hook and discussion prompt",
  "suggested_category": "Category name"
}
PROMPT;

        $userPrompt = "Draft Title: {$title}\nCategory: {$category}\nStream Notes / Topic:\n{$rawNotes}";

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 800,
        ];

        $res = $this->client->postJson('chat/completions', $payload, $this->model);
        if (!$res['success']) {
            return [
                'success' => true,
                'configured' => true,
                'fallback' => true,
                'suggested_title' => !empty($title) ? $title : 'Live Cultural Session',
                'caption' => (!empty($rawNotes) ? $rawNotes . "\n\n" : '') . 'Join our live broadcast on Kulturegram to participate in real-time cultural dialogue and ask questions!',
                'suggested_category' => $category,
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
            $fallbackCaption = !empty($clean) ? $clean : ((!empty($rawNotes) ? $rawNotes . "\n\n" : '') . "🔴 Streaming live on Kulturegram! Join the broadcast to ask questions in real time, connect with elders and community members, and learn about living African traditions.");
            return [
                'success' => true,
                'suggested_title' => $title ?: 'Live Cultural Gathering',
                'caption' => $fallbackCaption,
                'suggested_category' => $category,
            ];
        }

        $captionText = !empty($decoded['caption']) 
            ? $decoded['caption'] 
            : (!empty($decoded['description']) 
                ? $decoded['description'] 
                : (!empty($decoded['captivating_caption']) 
                    ? $decoded['captivating_caption'] 
                    : $rawNotes));

        if (empty(trim((string) $captionText))) {
            $captionText = (!empty($rawNotes) ? $rawNotes . "\n\n" : '') . "🔴 Streaming live on Kulturegram! Join the broadcast to ask questions in real time, connect with elders and community members, and learn about living African traditions.";
        }

        $titleText = !empty($decoded['suggested_title'])
            ? $decoded['suggested_title']
            : (!empty($decoded['title'])
                ? $decoded['title']
                : ($title ?: 'Live Cultural Broadcast'));

        $categoryText = !empty($decoded['suggested_category'])
            ? $decoded['suggested_category']
            : (!empty($decoded['category'])
                ? $decoded['category']
                : $category);

        return [
            'success' => true,
            'configured' => true,
            'suggested_title' => strip_tags(trim((string) $titleText)),
            'caption' => strip_tags(trim((string) $captionText)),
            'suggested_category' => strip_tags(trim((string) $categoryText)),
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
