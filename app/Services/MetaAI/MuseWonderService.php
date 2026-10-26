<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Log;

class MuseWonderService
{
    protected MetaModelApiClient $client;
    protected string $model;

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.wonder', 'muse-wonder');
    }

    /**
     * Run bounded preservation assessment on a cultural draft.
     *
     * @param array $draft Current draft attributes (name, region, description, category, etc.)
     * @param string|null $rawStory Original narrative or oral transcript
     * @return array Standardized result envelope with preservation plan
     */
    public function assessPreservationPlan(array $draft, ?string $rawStory = null): array
    {
        if (!$this->client->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'preservation_plan' => null,
                'model' => $this->model,
                'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your .env file.',
                'latency_ms' => 0.0,
            ];
        }

        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt = $this->buildUserPrompt($draft, $rawStory);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 4096,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success']) {
            return [
                'success' => false,
                'configured' => true,
                'preservation_plan' => null,
                'model' => $this->model,
                'error' => $response['error'] ?? 'Muse Wonder preservation assistant failed.',
                'status_code' => $response['status_code'],
                'latency_ms' => $response['latency_ms'],
            ];
        }

        $rawContent = $this->extractMessageContent($response['data']);
        $plan = $this->sanitizePreservationPlan($rawContent);

        if ($plan === null) {
            return [
                'success' => false,
                'configured' => true,
                'preservation_plan' => null,
                'model' => $this->model,
                'error' => 'Muse Wonder returned an invalid preservation plan payload.',
                'status_code' => 200,
                'latency_ms' => $response['latency_ms'],
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'preservation_plan' => $plan,
            'model' => $this->model,
            'error' => null,
            'status_code' => 200,
            'latency_ms' => $response['latency_ms'],
        ];
    }

    /**
     * System prompt with strict preservation bounds.
     */
    protected function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are Muse Wonder, a bounded cultural preservation assistant for Kulturegram.
You do NOT execute autonomous actions, publish records, alter permissions, or bypass human review.
Your sole role is to provide analytical preservation assistance across 5 structured dimensions:
1. Missing information audit: Identify what historical, geographic, or contextual information is missing from the record.
2. Neutral follow-up questions: Formulate 3 to 5 respectful, culturally neutral questions for community elders or custodians.
3. Verification flags: Highlight any claims, lineage statements, or sacred assertions that require explicit contributor verification.
4. Preservation summary: Provide a 2-sentence formal archival abstract of the heritage item.
5. Actionable preservation checklist: Provide a list of practical preservation steps (e.g. record elder song, document tool making, cross-reference archive).

Return your output EXCLUSIVELY in valid JSON matching this schema:
{
  "missing_information": ["item 1", "item 2"],
  "follow_up_questions": ["question 1", "question 2"],
  "claims_requiring_verification": ["claim 1", "claim 2"],
  "preservation_summary": "Two-sentence archival abstract",
  "preservation_checklist": [
    {"task": "string", "priority": "high|medium|low", "completed": false}
  ]
}
PROMPT;
    }

    protected function buildUserPrompt(array $draft, ?string $rawStory): string
    {
        $name = $draft['name'] ?? 'Untitled';
        $region = $draft['region'] ?? 'Unknown';
        $category = $draft['category'] ?? 'General';
        $desc = $draft['description'] ?? '';

        $prompt = "Cultural Heritage Record:\n";
        $prompt .= "- Title: {$name}\n";
        $prompt .= "- Region: {$region}\n";
        $prompt .= "- Category: {$category}\n";
        $prompt .= "- Description: {$desc}\n";

        if (!empty($rawStory)) {
            $prompt .= "\nOriginal Contributor Narrative / Oral Source:\n\"{$rawStory}\"\n";
        }

        $prompt .= "\nPlease generate the bounded preservation assessment.";
        return $prompt;
    }

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
     * Sanitize and validate preservation plan schema.
     */
    public function sanitizePreservationPlan(string $rawContent): ?array
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
            Log::warning('[MuseWonder] Failed to decode JSON from preservation plan', ['raw' => substr($clean, 0, 300)]);
            return null;
        }

        $missingInfo = array_map('strip_tags', (array) ($decoded['missing_information'] ?? []));
        $questions = array_map('strip_tags', (array) ($decoded['follow_up_questions'] ?? []));
        $verificationClaims = array_map('strip_tags', (array) ($decoded['claims_requiring_verification'] ?? []));
        $summary = strip_tags(trim((string) ($decoded['preservation_summary'] ?? '')));

        $checklist = [];
        if (isset($decoded['preservation_checklist']) && is_array($decoded['preservation_checklist'])) {
            foreach ($decoded['preservation_checklist'] as $item) {
                if (is_array($item) && !empty($item['task'])) {
                    $checklist[] = [
                        'task' => strip_tags((string) $item['task']),
                        'priority' => in_array($item['priority'] ?? '', ['high', 'medium', 'low'], true) ? $item['priority'] : 'medium',
                        'completed' => (bool) ($item['completed'] ?? false),
                    ];
                } elseif (is_string($item)) {
                    $checklist[] = [
                        'task' => strip_tags($item),
                        'priority' => 'medium',
                        'completed' => false,
                    ];
                }
            }
        }

        return [
            'missing_information' => array_values(array_filter($missingInfo)),
            'follow_up_questions' => array_values(array_filter($questions)),
            'claims_requiring_verification' => array_values(array_filter($verificationClaims)),
            'preservation_summary' => $summary,
            'preservation_checklist' => array_slice($checklist, 0, 10),
        ];
    }
}
