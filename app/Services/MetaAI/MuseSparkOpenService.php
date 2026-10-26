<?php

namespace App\Services\MetaAI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MuseSparkOpenService
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $model;
    protected int $timeout;
    protected string $provider;

    public function __construct()
    {
        $this->provider = (string) config('meta_ai.open_weight.provider', '');
        $this->endpoint = (string) config('meta_ai.open_weight.spark_open.endpoint', '');
        $this->apiKey = (string) config('meta_ai.open_weight.spark_open.api_key', '');
        $this->model = (string) config('meta_ai.open_weight.spark_open.model', 'meta-models/muse-spark-open-1026');
        $this->timeout = (int) config('meta_ai.open_weight.spark_open.timeout', 60);
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
     * Run structured cultural extraction using open-weight Muse Spark Open.
     *
     * @param string $story Narrative text
     * @return array Standardized result envelope
     */
    public function generateRecord(string $story): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'configured' => false,
                'model' => $this->model,
                'provider' => $this->getProviderName(),
                'record' => null,
                'error' => 'Muse Spark Open endpoint is not configured. Please set META_SPARK_OPEN_ENDPOINT in your environment (e.g. vLLM, Ollama, Together AI).',
                'latency_ms' => 0.0,
            ];
        }

        $startTime = microtime(true);
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are Muse Spark Open. Convert this cultural story into JSON: {"name": "title", "region": "region", "category": "Oral Traditions", "description": "summary", "significance": "meaning"}. Do not hallucinate.',
                ],
                [
                    'role' => 'user',
                    'content' => "Story:\n{$story}",
                ],
            ],
            'temperature' => 0.2,
            'max_tokens' => 1000,
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
                    'record' => is_array($parsed) ? $parsed : ['raw_summary' => $content],
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
                'record' => null,
                'error' => 'Endpoint returned HTTP status ' . $response->status() . ': ' . $response->body(),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            Log::warning('[MuseSparkOpen] Inference call failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'configured' => true,
                'model' => $this->model,
                'provider' => $this->getProviderName(),
                'record' => null,
                'error' => 'Failed to reach Spark Open endpoint: ' . $e->getMessage(),
                'latency_ms' => $latency,
            ];
        }
    }
}
