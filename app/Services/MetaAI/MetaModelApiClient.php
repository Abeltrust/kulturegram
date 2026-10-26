<?php

namespace App\Services\MetaAI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaModelApiClient
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $connectTimeout;

    public function __construct()
    {
        $this->apiKey = (string) config('meta_ai.api_key', '');
        $this->baseUrl = (string) config('meta_ai.base_url', 'https://api.meta.ai/v1');
        $this->timeout = (int) config('meta_ai.timeout', 45);
        $this->connectTimeout = (int) config('meta_ai.connect_timeout', 10);
    }

    /**
     * Get the active Meta Model API key.
     */
    public function getApiKey(): string
    {
        return trim((string) config('meta_ai.api_key', $this->apiKey));
    }

    /**
     * Get the active Meta Model API base URL.
     */
    public function getBaseUrl(): string
    {
        return rtrim((string) config('meta_ai.base_url', $this->baseUrl), '/');
    }

    /**
     * Check if the shared Meta Model API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getApiKey());
    }

    /**
     * Get masked API key for UI indicators (never reveals full secret).
     */
    public function getMaskedKey(): string
    {
        if (!$this->isConfigured()) {
            return 'NOT_CONFIGURED';
        }

        $key = $this->getApiKey();
        $length = strlen($key);
        if ($length <= 8) {
            return '••••••••';
        }

        return substr($key, 0, 4) . '••••' . substr($key, -4);
    }

    /**
     * Send a JSON POST request to a hosted Meta Model API endpoint.
     */
    public function postJson(string $endpoint, array $payload, string $model): array
    {
        if (!$this->isConfigured()) {
            return $this->unconfiguredResponse($model);
        }

        $url = $this->buildUrl($endpoint);
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getApiKey(),
                'Accept' => 'application/json',
                'User-Agent' => 'Kulturegram-LivingHeritage/1.0',
            ])
            ->connectTimeout($this->connectTimeout)
            ->timeout($this->timeout)
            ->post($url, $payload);

            $latency = round((microtime(true) - $startTime) * 1000, 2);
            return $this->handleResponse($response, $model, $latency, $endpoint);
        } catch (ConnectionException $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            $this->logError('Connection timeout to Meta Model API', $endpoint, $model, $e->getMessage());

            return [
                'success' => false,
                'configured' => true,
                'model' => $model,
                'data' => null,
                'raw_body' => null,
                'error' => 'Connection timed out while contacting Meta Model API. Please verify network and endpoint.',
                'status_code' => 504,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            $this->logError('Unexpected error communicating with Meta Model API', $endpoint, $model, $e->getMessage());

            return [
                'success' => false,
                'configured' => true,
                'model' => $model,
                'data' => null,
                'raw_body' => null,
                'error' => 'Failed to communicate with Meta Model API: ' . $e->getMessage(),
                'status_code' => 500,
                'latency_ms' => $latency,
            ];
        }
    }

    /**
     * Send a multipart/form-data POST request for media (audio/image) inputs.
     *
     * @param string $endpoint Endpoint relative to base URL
     * @param array $multipart Array of items formatted for Http::attach() or form fields
     * @param string $model Model identifier
     * @return array Standardized response array
     */
    public function postMultipart(string $endpoint, array $multipart, string $model): array
    {
        if (!$this->isConfigured()) {
            return $this->unconfiguredResponse($model);
        }

        $url = $this->buildUrl($endpoint);
        $startTime = microtime(true);

        try {
            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getApiKey(),
                'Accept' => 'application/json',
                'User-Agent' => 'Kulturegram-LivingHeritage/1.0',
            ])
            ->connectTimeout($this->connectTimeout)
            ->timeout($this->timeout);

            $elements = [];
            foreach ($multipart as $item) {
                $element = [
                    'name' => (string) $item['name'],
                    'contents' => $item['contents'] ?? '',
                ];
                if (!empty($item['filename'])) {
                    $element['filename'] = $item['filename'];
                }
                if (!empty($item['headers'])) {
                    $element['headers'] = $item['headers'];
                }
                $elements[] = $element;
            }

            $response = $request->asMultipart()->post($url, $elements);
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return $this->handleResponse($response, $model, $latency, $endpoint);
        } catch (ConnectionException $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            $this->logError('Connection timeout during multipart upload', $endpoint, $model, $e->getMessage());

            return [
                'success' => false,
                'configured' => true,
                'model' => $model,
                'data' => null,
                'raw_body' => null,
                'error' => 'Connection timed out while uploading media to Meta Model API.',
                'status_code' => 504,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            $this->logError('Error during multipart upload to Meta Model API', $endpoint, $model, $e->getMessage());

            return [
                'success' => false,
                'configured' => true,
                'model' => $model,
                'data' => null,
                'raw_body' => null,
                'error' => 'Failed to process media request with Meta Model API: ' . $e->getMessage(),
                'status_code' => 500,
                'latency_ms' => $latency,
            ];
        }
    }

    /**
     * Process response into a normalized response envelope.
     */
    protected function handleResponse(Response $response, string $model, float $latency, string $endpoint): array
    {
        $status = $response->status();
        $body = $response->body();
        $json = $response->json();

        if ($response->successful()) {
            return [
                'success' => true,
                'configured' => true,
                'model' => $model,
                'data' => is_array($json) ? $json : ['raw' => $body],
                'raw_body' => $body,
                'error' => null,
                'status_code' => $status,
                'latency_ms' => $latency,
            ];
        }

        // Handle specific failure modes
        $errorMsg = match ($status) {
            401 => 'Authentication failed. Please verify your META_MODEL_API_KEY.',
            403 => "Model '{$model}' is not accessible with this account or hackathon credits are pending.",
            404 => "Endpoint or model '{$model}' not found at {$endpoint}.",
            422 => 'The request payload was rejected by the model API as unprocessable.',
            429 => 'Rate limit exceeded on Meta Model API. Please wait a moment before trying again.',
            500, 502, 503 => 'Meta Model API upstream service error. Please try again later.',
            default => 'Meta Model API returned HTTP status ' . $status . '.',
        };

        // Extract any error message provided by upstream
        if (is_array($json) && !empty($json['error'])) {
            $upstreamDetail = is_array($json['error']) ? ($json['error']['message'] ?? json_encode($json['error'])) : (string) $json['error'];
            $errorMsg .= ' Detail: ' . $upstreamDetail;
        }

        $this->logError('Meta Model API request failed', $endpoint, $model, "Status {$status}: {$errorMsg}");

        return [
            'success' => false,
            'configured' => true,
            'model' => $model,
            'data' => is_array($json) ? $json : null,
            'raw_body' => $body,
            'error' => $errorMsg,
            'status_code' => $status,
            'latency_ms' => $latency,
        ];
    }

    /**
     * Standard response when key is missing or blank.
     */
    protected function unconfiguredResponse(string $model): array
    {
        return [
            'success' => false,
            'configured' => false,
            'model' => $model,
            'data' => null,
            'raw_body' => null,
            'error' => 'Meta Model API key is not configured. Please add META_MODEL_API_KEY to your .env file.',
            'status_code' => 503,
            'latency_ms' => 0.0,
        ];
    }

    /**
     * Build full URL from relative or absolute endpoint.
     */
    protected function buildUrl(string $endpoint): string
    {
        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        return $this->getBaseUrl() . '/' . ltrim($endpoint, '/');
    }

    /**
     * Redacted error logging (secrets never written to disk).
     */
    protected function logError(string $message, string $endpoint, string $model, string $details): void
    {
        Log::warning("[MetaAI] {$message}", [
            'model' => $model,
            'endpoint' => $endpoint,
            'details' => $details,
            // Sensitive tokens are strictly omitted
        ]);
    }
}
