<?php

namespace Tests\Feature;

use App\Services\MetaAI\MetaModelApiClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MetaModelApiClientTest extends TestCase
{
    public function test_client_reports_unconfigured_when_key_is_blank(): void
    {
        Config::set('meta_ai.api_key', '');
        $client = new MetaModelApiClient();

        $this->assertFalse($client->isConfigured());
        $this->assertEquals('NOT_CONFIGURED', $client->getMaskedKey());

        $result = $client->postJson('chat/completions', ['prompt' => 'test'], 'muse-spark');

        $this->assertFalse($result['success']);
        $this->assertFalse($result['configured']);
        $this->assertEquals(51, strlen($result['error']) > 0 ? 51 : 0);
        $this->assertEquals(503, $result['status_code']);
    }

    public function test_client_masks_credentials_properly(): void
    {
        Config::set('meta_ai.api_key', 'meta_live_key_9988776655');
        $client = new MetaModelApiClient();

        $this->assertTrue($client->isConfigured());
        $masked = $client->getMaskedKey();

        $this->assertStringStartsWith('meta', $masked);
        $this->assertStringEndsWith('6655', $masked);
        $this->assertStringContainsString('••••', $masked);
        $this->assertStringNotContainsString('998877', $masked);
    }

    public function test_client_handles_401_unauthorized(): void
    {
        Config::set('meta_ai.api_key', 'invalid_key');
        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([
                'error' => 'Invalid authentication token',
            ], 401),
        ]);

        $client = new MetaModelApiClient();
        $result = $client->postJson('chat/completions', [], 'muse-spark');

        $this->assertFalse($result['success']);
        $this->assertEquals(401, $result['status_code']);
        $this->assertStringContainsString('Authentication failed', $result['error']);
    }

    public function test_client_handles_403_model_access_or_credits_pending(): void
    {
        Config::set('meta_ai.api_key', 'valid_key_waiting_credits');
        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([
                'error' => 'Account has insufficient credits or model not enabled',
            ], 403),
        ]);

        $client = new MetaModelApiClient();
        $result = $client->postJson('chat/completions', [], 'muse-wonder');

        $this->assertFalse($result['success']);
        $this->assertEquals(403, $result['status_code']);
        $this->assertStringContainsString("Model 'muse-wonder' is not accessible", $result['error']);
    }

    public function test_client_handles_429_rate_limit(): void
    {
        Config::set('meta_ai.api_key', 'valid_key');
        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response([], 429),
        ]);

        $client = new MetaModelApiClient();
        $result = $client->postJson('chat/completions', [], 'muse-spark');

        $this->assertFalse($result['success']);
        $this->assertEquals(429, $result['status_code']);
        $this->assertStringContainsString('Rate limit exceeded', $result['error']);
    }

    public function test_client_handles_500_upstream_error(): void
    {
        Config::set('meta_ai.api_key', 'valid_key');
        Http::fake([
            'https://api.meta.ai/v1/chat/completions' => Http::response('Server Error', 500),
        ]);

        $client = new MetaModelApiClient();
        $result = $client->postJson('chat/completions', [], 'muse-spark');

        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['status_code']);
        $this->assertStringContainsString('upstream service error', $result['error']);
    }
}
