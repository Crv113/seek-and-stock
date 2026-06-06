<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function apiKeyHeader(): array
    {
        return ['Authorization' => 'Bearer '.config('custom.api_key')];
    }

    public function test_index_returns_users_with_discord_username(): void
    {
        $user = User::factory()->create(['discord_username' => 'mx_rider']);

        $response = $this->getJson('/api/users', $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $user->id,
            'discord_username' => 'mx_rider',
        ]);
    }

    public function test_index_requires_api_key(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    public function test_show_returns_user_with_discord_username(): void
    {
        $user = User::factory()->create(['discord_username' => 'mx_rider']);

        $response = $this->getJson("/api/users/{$user->id}", $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertJsonFragment(['discord_username' => 'mx_rider']);
    }

    public function test_show_requires_api_key(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(401);
    }
}
