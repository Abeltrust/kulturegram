<?php

namespace App\Services\MetaAI;

use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LiveStreamModerationService
{
    protected MetaModelApiClient $client;
    protected string $model;

    // Platform rule definitions for transparency
    public const RULE_HARASSMENT = 'Community Safety: Targeted Harassment & Bullying';
    public const RULE_HATE_SPEECH = 'Community Safety: Hate Speech & Cultural Discrimination';
    public const RULE_PRIVACY = 'Privacy Sovereignty: Unauthorized PII Exposure';
    public const RULE_SPAM = 'Civil Discourse: Commercial Spam & Repeated Disruption';
    public const RULE_HARMFUL = 'Community Integrity: Dangerous or Harmful Content';

    public function __construct(MetaModelApiClient $client)
    {
        $this->client = $client;
        $this->model = (string) config('meta_ai.models.spark', 'muse-spark-1.3');
    }

    /**
     * Non-blocking message processor: stores the message immediately,
     * then applies real-time contextual moderation.
     */
    public function processMessage(LiveStream $stream, User $sender, string $messageText): LiveStreamMessage
    {
        // 1. Immediately create message record (chat is never blocked)
        $message = LiveStreamMessage::create([
            'live_stream_id' => $stream->id,
            'user_id' => $sender->id,
            'message' => trim($messageText),
            'is_flagged' => false,
            'flag_status' => 'clean',
        ]);

        // 2. Run contextual AI moderation analysis
        try {
            $analysis = $this->analyzeMessage($stream, $messageText, $sender);

            if ($analysis['should_flag']) {
                $message->update([
                    'is_flagged' => true,
                    'flag_status' => 'flagged',
                    'flag_category' => $analysis['category'],
                    'flag_reason' => $analysis['reason'],
                    'violated_rule' => $analysis['violated_rule'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[LiveModeration] Analysis failed gracefully', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $message->fresh(['user']);
    }

    /**
     * Contextual analysis using Meta Model API with cultural nuance awareness.
     */
    public function analyzeMessage(LiveStream $stream, string $messageText, ?User $sender = null): array
    {
        $clean = trim($messageText);
        if (empty($clean)) {
            return ['should_flag' => false, 'category' => null, 'reason' => null, 'violated_rule' => null];
        }

        // Fast-path heuristic detection for immediate PII exposure
        if (preg_match('/(?:\b\d{3}[-.\s]??\d{3}[-.\s]??\d{4}\b|\b\d{10,11}\b)/', $clean)) {
            return [
                'should_flag' => true,
                'category' => 'privacy_violation',
                'reason' => 'Detected potential private phone number in public live chat.',
                'violated_rule' => self::RULE_PRIVACY,
            ];
        }

        // If client is not configured, fall back to graceful clean state
        if (!$this->client->isConfigured()) {
            return ['should_flag' => false, 'category' => null, 'reason' => null, 'violated_rule' => null];
        }

        $systemPrompt = $this->buildModerationSystemPrompt();
        $userPrompt = $this->buildModerationUserPrompt($stream, $clean);

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.1,
            'max_tokens' => 1024,
        ];

        $response = $this->client->postJson('chat/completions', $payload, $this->model);

        if (!$response['success']) {
            return ['should_flag' => false, 'category' => null, 'reason' => null, 'violated_rule' => null];
        }

        $raw = $this->extractContent($response['data']);
        return $this->parseModerationResponse($raw);
    }

    /**
     * Moderator Action: Dismiss flag (approves message and restores clean status).
     */
    public function dismissFlag(LiveStreamMessage $message, User $moderator): bool
    {
        return $message->update([
            'is_flagged' => false,
            'flag_status' => 'dismissed',
            'reviewed_by' => $moderator->id,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Moderator Action: Remove message from stream chat.
     */
    public function removeMessage(LiveStreamMessage $message, User $moderator): bool
    {
        return $message->update([
            'is_flagged' => true,
            'flag_status' => 'removed',
            'reviewed_by' => $moderator->id,
            'reviewed_at' => now(),
        ]);
    }

    protected function buildModerationSystemPrompt(): string
    {
        return <<<PROMPT
You are Kulturegram's Live Stream AI Moderator.
Your mission is to help human hosts maintain safe, engaging, and culturally enriching live stream discussions.

CRITICAL CULTURAL MODERATION PRINCIPLES:
1. UNDERSTAND CULTURAL CONTEXT: African and global cultural traditions involve passionate debates about chieftaincy titles, historical dynasties, tribal histories, and rituals. DO NOT flag healthy disagreements, historical arguments, dialect expressions, or traditional proverbs.
2. DO NOT flag messages solely for being off-topic or playful banter. Users have freedom to connect casually.
3. ONLY flag messages that present genuine violations:
   - Targeted harassment, personal insults, bullying.
   - Hate speech against ethnic groups, religions, or cultural identities.
   - Doxxing, publishing private phone numbers, home addresses, or personal contact details.
   - Commercial spam, scam links, crypto bots, repetitive disruption.
   - Threats of violence or harmful content.
4. Always provide an empathetic, constructive reason and state the violated platform rule.

Return response EXCLUSIVELY as valid JSON matching this schema:
{
  "should_flag": boolean,
  "category": "harassment|hate_speech|spam|privacy_violation|harmful_content|derailment|null",
  "reason": "Clear explanation of why this was flagged, or null",
  "violated_rule": "Platform rule title, or null"
}
PROMPT;
    }

    protected function buildModerationUserPrompt(LiveStream $stream, string $message): string
    {
        $title = $stream->title ?? 'Live Discussion';
        $desc = $stream->description ?? 'Cultural community live broadcast';
        $category = $stream->category ?? 'General';

        return <<<PROMPT
Live Stream Context:
- Title: {$title}
- Category: {$category}
- Topic Description: {$desc}

Chat Message to evaluate:
"{$message}"

Analyze whether this message should be flagged according to platform safety rules.
PROMPT;
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

    protected function parseModerationResponse(string $raw): array
    {
        $clean = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{[\s\S]*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return ['should_flag' => false, 'category' => null, 'reason' => null, 'violated_rule' => null];
        }

        $shouldFlag = (bool) ($decoded['should_flag'] ?? false);
        $category = !empty($decoded['category']) ? strip_tags(trim((string) $decoded['category'])) : null;
        $reason = !empty($decoded['reason']) ? strip_tags(trim((string) $decoded['reason'])) : null;
        $rule = !empty($decoded['violated_rule']) ? strip_tags(trim((string) $decoded['violated_rule'])) : null;

        return [
            'should_flag' => $shouldFlag,
            'category' => $category,
            'reason' => $reason,
            'violated_rule' => $rule,
        ];
    }
}
