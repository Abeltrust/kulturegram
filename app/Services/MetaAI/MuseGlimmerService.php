<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MuseGlimmerService
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $model;
    protected int $timeout;
    protected string $provider;

    public function __construct()
    {
        $this->provider = (string) config('meta_ai.open_weight.provider', '');
        $this->endpoint = (string) config('meta_ai.open_weight.glimmer.endpoint', '');
        $this->apiKey = (string) config('meta_ai.open_weight.glimmer.api_key', '');
        $this->model = (string) config('meta_ai.open_weight.glimmer.model', 'meta-models/muse-glimmer-8b');
        $this->timeout = (int) config('meta_ai.open_weight.glimmer.timeout', 60);
    }

    public function isConfigured(): bool
    {
        return !empty(trim($this->endpoint));
    }

    public function getProviderName(): string
    {
        return !empty($this->provider) ? $this->provider : 'Independent Inference Endpoint';
    }

    /**
     * Run cultural claim verification and taxonomy tagging using open-weight Muse Glimmer.
     *
     * @param string $story Narrative text
     * @return array Standardized result envelope
     */
    public function analyzeTagsAndClaims(string $story): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'model' => $this->model,
                'provider' => $this->getProviderName(),
                'tags' => [],
                'claim_flags' => [],
                'error' => 'Muse Glimmer open-weight endpoint is not configured. Please set META_GLIMMER_ENDPOINT in your environment (vLLM, Ollama, HuggingFace, etc.).',
                'latency_ms' => 0.0,
            ];
        }

        $startTime = microtime(true);
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are Muse Glimmer (Open-Weight). Extract 5 relevant cultural archive taxonomy tags and list any unverified factual claims in JSON format: {"tags": ["tag1"], "claim_flags": ["claim1"]}.',
                ],
                [
                    'role' => 'user',
                    'content' => "Cultural story:\n\"\"\"\n{$story}\n\"\"\"",
                ],
            ],
            'temperature' => 0.2,
            'max_tokens' => 500,
        ];

        try {
            $request = Http::timeout($this->timeout);
            if (!empty($this->apiKey)) {
                $request->withToken($this->apiKey);
            }

            $response = $request->post($this->endpoint, $payload);
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $json = $response->json();
                $content = $json['choices'][0]['message']['content'] ?? ($json['content'] ?? '');
                $clean = trim(preg_replace('/^```[a-z]*\s*|\n?```$/', '', $content));
                $parsed = json_decode($clean, true);

                return [
                    'success' => true,
                    'configured' => true,
                    'model' => $this->model,
                    'provider' => $this->getProviderName(),
                    'tags' => (array) ($parsed['tags'] ?? []),
                    'claim_flags' => (array) ($parsed['claim_flags'] ?? []),
                    'raw_output' => $content,
                    'error' => null,
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => false,
                'configured' => true,
                'model' => $this->model,
                'provider' => $this->getProviderName(),
                'tags' => [],
                'claim_flags' => [],
                'error' => 'Endpoint returned HTTP status ' . $response->status() . ': ' . $response->body(),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            Log::warning('[MuseGlimmer] Inference call failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'configured' => true,
                'model' => $this->model,
                'provider' => $this->getProviderName(),
                'tags' => [],
                'claim_flags' => [],
                'error' => 'Failed to reach Glimmer inference endpoint: ' . $e->getMessage(),
                'latency_ms' => $latency,
            ];
        }
    }
}
