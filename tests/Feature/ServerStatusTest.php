<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ServerStatusTest extends TestCase
{
    use RefreshDatabase;

    private function apiKeyHeader(): array
    {
        return ['Authorization' => 'Bearer '.config('custom.api_key')];
    }

    public function test_post_server_status_with_valid_api_key_returns_success_and_stores_cache(): void
    {
        $response = $this->postJson('/api/server-status', ['players_online' => 12], $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertExactJson(['success' => true]);
        $this->assertEquals(12, Cache::get('server_status:players_online'));
    }

    public function test_post_server_status_without_api_key_returns_401(): void
    {
        $response = $this->postJson('/api/server-status', ['players_online' => 12]);

        $response->assertStatus(401);
    }

    public function test_post_server_status_with_invalid_api_key_returns_401(): void
    {
        $response = $this->postJson('/api/server-status', ['players_online' => 12], [
            'Authorization' => 'Bearer invalid-key',
        ]);

        $response->assertStatus(401);
    }

    public function test_post_server_status_without_players_online_returns_422(): void
    {
        $response = $this->postJson('/api/server-status', [], $this->apiKeyHeader());

        $response->assertStatus(422);
    }

    public function test_post_server_status_with_non_numeric_players_online_returns_422(): void
    {
        $response = $this->postJson('/api/server-status', ['players_online' => 'abc'], $this->apiKeyHeader());

        $response->assertStatus(422);
    }

    public function test_post_server_status_with_negative_players_online_returns_422(): void
    {
        $response = $this->postJson('/api/server-status', ['players_online' => -1], $this->apiKeyHeader());

        $response->assertStatus(422);
    }

    public function test_get_server_status_with_cached_value_returns_it(): void
    {
        Cache::put('server_status:players_online', 42, now()->addSeconds(90));

        $response = $this->getJson('/api/server-status', $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertExactJson(['data' => ['players_online' => 42]]);
    }

    public function test_get_server_status_without_api_key_returns_401(): void
    {
        $response = $this->getJson('/api/server-status');

        $response->assertStatus(401);
    }

    public function test_get_server_status_with_empty_cache_returns_null(): void
    {
        Cache::forget('server_status:players_online');

        $response = $this->getJson('/api/server-status', $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertExactJson(['data' => ['players_online' => null]]);
    }

    public function test_get_server_status_with_zero_players_online_returns_zero_distinct_from_null(): void
    {
        Cache::put('server_status:players_online', 0, now()->addSeconds(90));

        $response = $this->getJson('/api/server-status', $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertExactJson(['data' => ['players_online' => 0]]);
    }
}
