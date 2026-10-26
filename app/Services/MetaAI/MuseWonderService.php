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

    /**
     * Run an AI-powered Cultural Fact-Check & Originality Audit.
     * Evaluates oral authenticity, generic/plagiarism risk against standard reference materials,
     * historical assertion consistency, and flags claims needing elder verification.
     *
     * @param string $sourceStory Original oral narrative or transcription
     * @param array $draft Structured cultural draft fields
     * @return array Standardized result envelope with originality audit
     */
    public function auditOriginalityAndFacts(string $sourceStory, array $draft = []): array
    {
        $cleanStory = trim($sourceStory);
        if (empty($cleanStory)) {
            return [
                'success' => false,
                'configured' => $this->client->isConfigured(),
                'audit' => null,
                'model' => $this->model,
                'error' => 'Source story or transcript is required for originality and fact-check audit.',
                'latency_ms' => 0.0,
            ];
        }

        if (!$this->client->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'audit' => null,
                'model' => $this->model,
                'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your .env file.',
                'latency_ms' => 0.0,
            ];
        }

        $systemPrompt = $this->buildAuditSystemPrompt();
        $userPrompt = $this->buildAuditUserPrompt($cleanStory, $draft);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 4096,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success']) {
            return [
                'success' => false,
                'configured' => true,
                'audit' => null,
                'model' => $this->model,
                'error' => $response['error'] ?? 'Muse Wonder originality audit failed.',
                'status_code' => $response['status_code'] ?? 500,
                'latency_ms' => $response['latency_ms'] ?? 0.0,
            ];
        }

        $rawContent = $this->extractMessageContent($response['data']);
        $audit = $this->sanitizeOriginalityAudit($rawContent);

        if ($audit === null) {
            return [
                'success' => false,
                'configured' => true,
                'audit' => null,
                'model' => $this->model,
                'error' => 'Muse Wonder returned an unparseable originality audit payload.',
                'status_code' => 200,
                'latency_ms' => $response['latency_ms'] ?? 0.0,
            ];
        }

        return [
            'success' => true,
            'configured' => true,
            'audit' => $audit,
            'model' => $this->model,
            'error' => null,
            'status_code' => 200,
            'latency_ms' => $response['latency_ms'] ?? 0.0,
        ];
    }

    protected function buildAuditSystemPrompt(): string
    {
        return <<<PROMPT
You are Muse Wonder, a bounded cultural preservation and archival integrity assistant for Kulturegram.
Your task is to conduct an Originality, Plagiarism, and Factual Integrity Audit on a submitted cultural heritage record.

CULTURAL AUDIT PRINCIPLES:
1. Oral traditions, folklore, and proverbs are shared communal heritage. Do NOT treat shared ancestral folktales as plagiarism unless the narrative is a verbatim copy-paste of a published modern textbook or encyclopedia article (e.g. Wikipedia).
2. Measure "oral_authenticity_score" (0-100): High scores (75-100) reflect personal memory, clan recollections, conversational cadence, dialect words, and local context. Low scores (0-45) reflect generic, encyclopedic, or tourist brochure summaries.
3. Measure "plagiarism_risk" ("low", "medium", "high"): "high" means phrasing closely matches public web articles or published books; "medium" means mixed; "low" means original firsthand account.
4. "historical_fact_check_flags": Identify any specific dates, royal lineages, deity origins, or historical assertions that require confirmation with community elders or archival custodians.
5. "fidelity_check": Check if the structured draft remains faithful to the contributor's raw story without invented details.

Return your response EXCLUSIVELY in valid JSON matching this schema:
{
  "oral_authenticity_score": 85,
  "authenticity_label": "High Firsthand Oral Fidelity",
  "plagiarism_risk": "low",
  "plagiarism_risk_reason": "Explanation of the assessment",
  "suspected_source_parallels": ["phrase or source type if any"],
  "fidelity_check": {
    "is_faithful_to_source": true,
    "hallucination_warnings": []
  },
  "historical_fact_check_flags": [
    {
      "claim": "Specific historical assertion",
      "verification_status": "consistent_with_known_history|requires_elder_verification|potential_anachronism",
      "guidance": "Recommended elder question or archive check"
    }
  ],
  "moderator_recommendations": ["Archival advice for reviewer"]
}
PROMPT;
    }

    protected function buildAuditUserPrompt(string $cleanStory, array $draft): string
    {
        $prompt = "Source Cultural Narrative / Oral Transcript:\n\"\"\"\n{$cleanStory}\n\"\"\"\n\n";

        if (!empty($draft)) {
            $prompt .= "Draft Metadata:\n";
            if (!empty($draft['name'])) $prompt .= "- Title: " . $draft['name'] . "\n";
            if (!empty($draft['region'])) $prompt .= "- Region: " . $draft['region'] . "\n";
            if (!empty($draft['category'])) $prompt .= "- Category: " . $draft['category'] . "\n";
            if (!empty($draft['description'])) $prompt .= "- Description: " . $draft['description'] . "\n";
        }

        $prompt .= "\nPlease generate the Originality, Plagiarism, and Factual Integrity Audit.";
        return $prompt;
    }

    public function sanitizeOriginalityAudit(string $rawContent): ?array
    {
        $clean = trim($rawContent);

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{[\s\S]*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('[MuseWonder] Failed to decode JSON from originality audit', ['raw' => substr($clean, 0, 300)]);
            return null;
        }

        $score = isset($decoded['oral_authenticity_score']) ? (int) $decoded['oral_authenticity_score'] : 70;
        $score = max(0, min(100, $score));

        $risk = strtolower(trim((string) ($decoded['plagiarism_risk'] ?? 'low')));
        if (!in_array($risk, ['low', 'medium', 'high'], true)) {
            $risk = 'low';
        }

        $flags = [];
        if (isset($decoded['historical_fact_check_flags']) && is_array($decoded['historical_fact_check_flags'])) {
            foreach ($decoded['historical_fact_check_flags'] as $flag) {
                if (is_array($flag) && !empty($flag['claim'])) {
                    $status = in_array($flag['verification_status'] ?? '', ['consistent_with_known_history', 'requires_elder_verification', 'potential_anachronism'], true)
                        ? $flag['verification_status']
                        : 'requires_elder_verification';

                    $flags[] = [
                        'claim' => strip_tags((string) $flag['claim']),
                        'verification_status' => $status,
                        'guidance' => strip_tags((string) ($flag['guidance'] ?? '')),
                    ];
                }
            }
        }

        return [
            'oral_authenticity_score' => $score,
            'authenticity_label' => strip_tags(trim((string) ($decoded['authenticity_label'] ?? 'Oral Heritage Assessment'))),
            'plagiarism_risk' => $risk,
            'plagiarism_risk_reason' => strip_tags(trim((string) ($decoded['plagiarism_risk_reason'] ?? 'Evaluated for oral cadence and source fidelity.'))),
            'suspected_source_parallels' => array_map('strip_tags', (array) ($decoded['suspected_source_parallels'] ?? [])),
            'fidelity_check' => [
                'is_faithful_to_source' => (bool) ($decoded['fidelity_check']['is_faithful_to_source'] ?? true),
                'hallucination_warnings' => array_map('strip_tags', (array) ($decoded['fidelity_check']['hallucination_warnings'] ?? [])),
            ],
            'historical_fact_check_flags' => array_slice($flags, 0, 10),
            'moderator_recommendations' => array_map('strip_tags', (array) ($decoded['moderator_recommendations'] ?? [])),
            'audited_at' => now()->toIso8601String(),
        ];
    }
}
