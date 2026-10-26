<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Log;

class MuseSparkService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public const VALID_CATEGORIES = [
        'Traditional Crafts',
        'Oral Traditions',
        'Music & Dance',
        'Culinary Heritage',
        'Religious Practices',
        'Festivals & Ceremonies',
        'Language & Literature',
        'Architecture & Design',
        'Agricultural Practices',
        'Healing Traditions',
        'Social Customs',
        'Art & Aesthetics',
        'Games & Sports',
        'Clothing & Textiles',
    ];

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.spark', 'muse-spark');
    }

    /**
     * Convert raw oral history or narrative text into a structured cultural record.
     *
     * @param string $sourceText Contributor's approved narrative or audio transcript
     * @param array $context Optional user hints (region, language, contributor name)
     * @return array Standardized result envelope
     */
    public function prepareStory(string $sourceText, array $context = []): array
    {
        $cleanSource = trim($sourceText);
        if (empty($cleanSource)) {
            return [
                'success' => false,
                'configured' => $this->client->isConfigured(),
                'record' => null,
                'model' => $this->model,
                'error' => 'Source story or transcript cannot be empty.',
                'latency_ms' => 0.0,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'record' => null,
                'model' => $this->model,
                'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your .env file.',
                'latency_ms' => 0.0,
            ];
        }

        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt = $this->buildUserPrompt($cleanSource, $context);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2, // Low temperature for high factual discipline
            'max_tokens' => 4096,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success']) {
            return [
                'success' => false,
                'configured' => true,
                'record' => null,
                'model' => $this->model,
                'error' => $response['error'] ?? 'Muse Spark cultural record generation failed.',
                'status_code' => $response['status_code'],
                'latency_ms' => $response['latency_ms'],
            ];
        }

        // Parse and validate the response
        $rawContent = $this->extractMessageContent($response['data']);
        $parsedRecord = $this->validateAndSanitizeRecord($rawContent);

        if ($parsedRecord === null) {
            return [
                'success' => false,
                'configured' => true,
                'record' => null,
                'model' => $this->model,
                'error' => 'Muse Spark returned a response that could not be parsed into a verified cultural record schema.',
                'status_code' => 200,
                'latency_ms' => $response['latency_ms'],
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'record' => $parsedRecord,
            'model' => $this->model,
            'error' => null,
            'status_code' => 200,
            'latency_ms' => $response['latency_ms'],
        ];
    }

    /**
     * System prompt with strict factual integrity rules.
     */
    protected function buildSystemPrompt(): string
    {
        $categoriesList = implode(', ', self::VALID_CATEGORIES);

        return <<<PROMPT
You are Muse Spark, an expert cultural preservation assistant for Kulturegram.
Your mission is to convert oral histories, indigenous knowledge, folktales, and cultural memories into accurate, structured archival drafts.

CRITICAL ACCURACY & CULTURAL INTEGRITY RULES:
1. NEVER invent, fabricate, or assume historical dates, sacred rituals, translations, or lineages.
2. If information is not mentioned in the source story, set that field to null or an empty string. DO NOT guess.
3. Preserve original indigenous language words, names, greetings, and expressions exactly as uttered.
4. Categorize strictly into one of these allowed categories: [{$categoriesList}]. If uncertain, select "Oral Traditions".
5. Under "missing_information_warnings", list any key cultural context missing from the account (e.g. origins, practitioner roles, seasonal timing).
6. Under "suggested_follow_up_questions", generate 2 to 4 respectful, culturally sensitive follow-up questions for the elder or contributor.
7. Return your response ONLY as valid JSON matching the exact schema specified, with NO surrounding Markdown fences or introductory chatter.

JSON SCHEMA:
{
  "name": "string (Short descriptive cultural title)",
  "region": "string or null (Community, town, state, or region)",
  "category": "string (Must be one of the allowed categories)",
  "description": "string (Thorough, respectful synthesis of the narrative, min 30 words)",
  "language": "string or null (Language or dialect spoken)",
  "historical_period": "string or null (Historical era or lineage if known)",
  "significance": "string or null (Cultural meaning and spiritual/social importance)",
  "rituals": "string or null (Ceremonies, rites, or customary steps described in source)",
  "community_role": "string or null (Who practices or holds this tradition: elders, youth, guilds)",
  "transmission_methods": "string or null (How knowledge is handed down: songs, apprenticeship, festivals)",
  "challenges": "string or null (Modern threats: urbanization, language loss, environmental shifts)",
  "tags": ["string", "string"],
  "missing_information_warnings": ["string"],
  "suggested_follow_up_questions": ["string"]
}
PROMPT;
    }

    /**
     * User prompt containing the raw source story.
     */
    protected function buildUserPrompt(string $sourceText, array $context): string
    {
        $prompt = "Source Cultural Story / Oral Transcript:\n\"\"\"\n{$sourceText}\n\"\"\"";

        if (!empty($context['region'])) {
            $prompt .= "\nContributor Region Hint: " . $context['region'];
        }
        if (!empty($context['language'])) {
            $prompt .= "\nContributor Language Hint: " . $context['language'];
        }

        $prompt .= "\n\nPlease extract the structured cultural record strictly following the required JSON schema.";
        return $prompt;
    }

    /**
     * Extract message text from chat completions payload.
     */
    protected function extractMessageContent(array $data): string
    {
        if (!empty($data['choices'][0]['message']['content'])) {
            return (string) $data['choices'][0]['message']['content'];
        }

        if (!empty($data['choices'][0]['message']['reasoning_content'])) {
            return (string) $data['choices'][0]['message']['reasoning_content'];
        }

        if (!empty($data['content'])) {
            return (string) $data['content'];
        }

        return '';
    }

    /**
     * Validate and sanitize untrusted AI output against schema.
     */
    public function validateAndSanitizeRecord(string $rawContent): ?array
    {
        $clean = trim($rawContent);

        // Extract JSON if wrapped in markdown code fence or embedded in explanatory text
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{[\s\S]*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('[MuseSpark] Failed to decode JSON from model output', ['raw' => substr($clean, 0, 300)]);
            return null;
        }

        // Validate category
        $category = trim((string) ($decoded['category'] ?? ''));
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            $category = 'Oral Traditions'; // Safe default
        }

        // Sanitize tags
        $tags = [];
        if (isset($decoded['tags']) && is_array($decoded['tags'])) {
            foreach ($decoded['tags'] as $tag) {
                $cleanTag = trim(preg_replace('/[^a-zA-Z0-9_\-\s]/', '', (string) $tag));
                if (!empty($cleanTag) && strlen($cleanTag) <= 30) {
                    $tags[] = strtolower($cleanTag);
                }
            }
        }

        // Format clean record
        return [
            'name' => strip_tags(trim((string) ($decoded['name'] ?? 'Untitled Cultural Heritage'))),
            'region' => !empty($decoded['region']) ? strip_tags(trim((string) $decoded['region'])) : null,
            'category' => $category,
            'description' => strip_tags(trim((string) ($decoded['description'] ?? ''))),
            'language' => !empty($decoded['language']) ? strip_tags(trim((string) $decoded['language'])) : null,
            'historical_period' => !empty($decoded['historical_period']) ? strip_tags(trim((string) $decoded['historical_period'])) : null,
            'significance' => !empty($decoded['significance']) ? strip_tags(trim((string) $decoded['significance'])) : null,
            'rituals' => !empty($decoded['rituals']) ? strip_tags(trim((string) $decoded['rituals'])) : null,
            'community_role' => !empty($decoded['community_role']) ? strip_tags(trim((string) $decoded['community_role'])) : null,
            'transmission_methods' => !empty($decoded['transmission_methods']) ? strip_tags(trim((string) $decoded['transmission_methods'])) : null,
            'challenges' => !empty($decoded['challenges']) ? strip_tags(trim((string) $decoded['challenges'])) : null,
            'tags' => array_slice(array_unique($tags), 0, 10),
            'missing_information_warnings' => is_array($decoded['missing_information_warnings'] ?? null)
                ? array_map('strip_tags', $decoded['missing_information_warnings'])
                : [],
            'suggested_follow_up_questions' => is_array($decoded['suggested_follow_up_questions'] ?? null)
                ? array_map('strip_tags', $decoded['suggested_follow_up_questions'])
                : [],
        ];
    }
}
