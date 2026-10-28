<?php

namespace App\Services\MetaAI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OralTraditionVoiceService
{
    protected MetaModelApiClient $client;
    protected MuseVoiceTranscribeService $transcribeService;
    protected string $model;

    public function __construct(MetaModelApiClient $client, MuseVoiceTranscribeService $transcribeService)
    {
        $this->client = $client;
        $this->transcribeService = $transcribeService;
        $this->model = (string) config('meta_ai.models.spark', 'muse-spark-1.3');
    }

    /**
     * Transcribe an uploaded audio file (field recording or voice note) into text.
     */
    public function transcribeAudio(UploadedFile|string $audioFile, ?string $languageHint = null): array
    {
        return $this->transcribeService->transcribe($audioFile, $languageHint);
    }

    /**
     * Format spoken oral testimony into a ready-to-post cultural heritage preservation record.
     *
     * @param string $spokenNarrative Raw voice dictation or spoken testimony
     * @param array $context Hints such as speaker name, region, record type, language
     * @return array Standardized formatted result envelope
     */
    public function formatOralRecord(string $spokenNarrative, array $context = []): array
    {
        $cleanSpoken = trim($spokenNarrative);
        if (empty($cleanSpoken)) {
            return [
                'success' => false,
                'error' => 'Spoken testimony cannot be empty.',
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => true,
                'configured' => false,
                'is_ai_generated' => false,
                'data' => $this->buildDeterministicCultureRecord($cleanSpoken, $context),
            ];
        }

        $systemPrompt = <<<PROMPT
You are Kulturegram's Oral Tradition & Cultural Preservation Archivist.
A cultural custodian or community member has spoken their heritage memories and traditions aloud into a microphone.
Your job is to arrange and format their spoken testimony into a structured, ready-to-post cultural preservation record.

MANDATES:
1. Remove speech disfluencies (e.g., 'um', 'uh', 'you know', 'like', repeated stammering) without erasing authentic idioms, proverbs, deity names, or indigenous vocabulary.
2. Structure the core description into 2 to 3 engaging, respectful paragraphs:
   - Paragraph 1: Origin, background, and cultural setting.
   - Paragraph 2: Core practice, ceremonial rites, craft steps, or oral narrative.
   - Paragraph 3: Cultural significance, wisdom lessons, and modern community meaning.
3. Propose a clear, captivating title.
4. Extract likely cultural group, region, language, and category if mentioned or hinted.
5. Generate 3-5 relevant cultural hashtags.
6. Return strictly valid JSON with no markdown tags.

JSON Schema:
{
  "proposed_title": "string",
  "formatted_description": "string (multi-paragraph formatted text)",
  "cultural_group": "string or unknown",
  "region": "string or unknown",
  "language": "string or unknown",
  "category": "string (Oral Traditions, Traditional Crafts, Music & Dance, Culinary Heritage, Religious Practices, Festivals & Ceremonies, Language & Literature, Architecture & Design, Healing Traditions, Social Customs)",
  "verbatim_transcript": "string",
  "suggested_tags": ["string"],
  "cultural_notes": ["string"]
}
PROMPT;

        $userPrompt = "CONTEXT HINTS: " . json_encode($context) . "\n\nSPOKEN ORAL TESTIMONY:\n" . $cleanSpoken;

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 2000,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success'] || empty($response['data'])) {
            return [
                'success' => true,
                'configured' => true,
                'is_ai_generated' => false,
                'data' => $this->buildDeterministicCultureRecord($cleanSpoken, $context),
                'notice' => 'Generated using intelligent rule-based formatter.',
            ];
        }

        $parsed = $this->parseJson($response['data']);
        if (!$parsed) {
            return [
                'success' => true,
                'configured' => true,
                'is_ai_generated' => false,
                'data' => $this->buildDeterministicCultureRecord($cleanSpoken, $context),
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'is_ai_generated' => true,
            'data' => $parsed,
        ];
    }

    /**
     * Format a spoken story into a ready-to-post timeline story.
     *
     * @param string $spokenContent Raw speech dictation
     * @param array $context Hints such as chapter, location, author name
     * @return array Standardized result envelope
     */
    public function formatTimelineStory(string $spokenContent, array $context = []): array
    {
        $cleanSpoken = trim($spokenContent);
        if (empty($cleanSpoken)) {
            return [
                'success' => false,
                'error' => 'Spoken story cannot be empty.',
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => true,
                'configured' => false,
                'is_ai_generated' => false,
                'data' => $this->buildDeterministicTimelineStory($cleanSpoken, $context),
            ];
        }

        $systemPrompt = <<<PROMPT
You are Kulturegram's Oral Storytelling AI Assistant.
A user has spoken their personal or cultural story aloud into the microphone.
Your job is to arrange and polish their spoken words into an engaging, ready-to-post story for the Timeline feed.

MANDATES:
1. Remove filler sounds (um, uh, you know, like) while keeping the speaker's authentic voice, tone, and cultural warmth.
2. Format into clean, easy-to-read paragraphs with natural punctuation.
3. Suggest 2-4 relevant cultural hashtags (e.g. #LivingHeritage, #OralTradition, #CulturalWisdom).
4. Propose an appropriate life chapter from: 'Roots & Foundation', 'Heritage Preservation', 'Community Building', 'Cultural Tech Innovation', 'University & Cultural Awakening', 'Software Architecture Mastery'.
5. Return strictly valid JSON.

JSON Schema:
{
  "formatted_content": "string (the polished post body ready to post)",
  "chapter": "string",
  "suggested_tags": ["string"],
  "summary": "string"
}
PROMPT;

        $userPrompt = "CONTEXT HINTS: " . json_encode($context) . "\n\nSPOKEN STORY:\n" . $cleanSpoken;

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 1200,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success'] || empty($response['data'])) {
            return [
                'success' => true,
                'configured' => true,
                'is_ai_generated' => false,
                'data' => $this->buildDeterministicTimelineStory($cleanSpoken, $context),
            ];
        }

        $parsed = $this->parseJson($response['data']);
        if (!$parsed) {
            return [
                'success' => true,
                'configured' => true,
                'is_ai_generated' => false,
                'data' => $this->buildDeterministicTimelineStory($cleanSpoken, $context),
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'is_ai_generated' => true,
            'data' => $parsed,
        ];
    }

    /**
     * Parse JSON from AI response safely.
     */
    protected function parseJson(mixed $data): ?array
    {
        if (is_array($data) && isset($data['choices'][0]['message']['content'])) {
            $raw = trim($data['choices'][0]['message']['content']);
        } elseif (is_string($data)) {
            $raw = trim($data);
        } else {
            return null;
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Clean filler words from raw spoken text.
     */
    public function cleanFillerWords(string $text): string
    {
        // Common conversational fillers
        $patterns = [
            '/\b(um|uh|er|ah)\b/i' => '',
            '/\b(you know|like you know|i mean like|kind of like)\b/i' => '',
            '/\s{2,}/' => ' ',
        ];

        $cleaned = preg_replace(array_keys($patterns), array_values($patterns), $text);
        return trim(ucfirst($cleaned));
    }

    /**
     * Deterministic rule-based formatter for cultural preservation records.
     */
    protected function buildDeterministicCultureRecord(string $spoken, array $context): array
    {
        $cleaned = $this->cleanFillerWords($spoken);
        $sentences = preg_split('/(?<=[.?!])\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        $title = $context['name'] ?? ($context['title'] ?? null);
        if (!$title) {
            $firstSentence = !empty($sentences) ? $sentences[0] : $cleaned;
            $words = preg_split('/\s+/', $firstSentence);
            $title = count($words) > 7 ? implode(' ', array_slice($words, 0, 7)) . '...' : $firstSentence;
        }

        // Structure into paragraphs
        if (count($sentences) >= 4) {
            $half = (int) ceil(count($sentences) / 2);
            $p1 = implode(' ', array_slice($sentences, 0, $half));
            $p2 = implode(' ', array_slice($sentences, $half));
            $formattedDescription = $p1 . "\n\n" . $p2;
        } else {
            $formattedDescription = $cleaned;
        }

        // Detect potential cultural tags
        $tags = ['OralTradition', 'LivingHeritage', 'CulturalPreservation'];
        if (!empty($context['cultural_group'])) {
            $tags[] = Str::slug($context['cultural_group']);
        }

        return [
            'proposed_title' => $title,
            'formatted_description' => $formattedDescription,
            'cultural_group' => $context['cultural_group'] ?? 'unknown',
            'region' => $context['region'] ?? 'unknown',
            'language' => $context['language'] ?? 'unknown',
            'category' => $context['category'] ?? 'Oral Traditions',
            'verbatim_transcript' => $spoken,
            'suggested_tags' => array_values(array_unique($tags)),
            'cultural_notes' => ['Transcribed and arranged from oral voice recording.'],
        ];
    }

    /**
     * Deterministic rule-based formatter for timeline stories.
     */
    protected function buildDeterministicTimelineStory(string $spoken, array $context): array
    {
        $cleaned = $this->cleanFillerWords($spoken);
        $sentences = preg_split('/(?<=[.?!])\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        if (count($sentences) >= 3) {
            $first = implode(' ', array_slice($sentences, 0, 2));
            $rest = implode(' ', array_slice($sentences, 2));
            $formatted = $first . "\n\n" . $rest;
        } else {
            $formatted = $cleaned;
        }

        // Append hashtags if not already present
        if (!str_contains($formatted, '#')) {
            $formatted .= "\n\n#OralStory #Heritage #Kulturegram";
        }

        return [
            'formatted_content' => $formatted,
            'chapter' => $context['chapter'] ?? 'Heritage Preservation',
            'suggested_tags' => ['OralStory', 'Heritage', 'Kulturegram'],
            'summary' => Str::limit($cleaned, 100),
        ];
    }
}
