<?php

namespace Tests\Feature;

use App\Models\AnonymousUser;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UsersAndPlayersCursorPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Config::set('custom.default_page_size', 100);
    }

    private function apiKeyHeader(): array
    {
        return ['Authorization' => 'Bearer '.config('custom.api_key')];
    }

    public function test_users_index_paginates_and_next_cursor_becomes_null_at_end(): void
    {
        User::factory()->count(110)->create();

        $firstPage = $this->getJson('/api/users', $this->apiKeyHeader());

        $firstPage->assertStatus(200);
        $firstPage->assertJsonCount(100, 'data');
        $this->assertNotNull($firstPage->json('next_cursor'));

        $secondPage = $this->getJson('/api/users?cursor='.$firstPage->json('next_cursor'), $this->apiKeyHeader());

        $secondPage->assertStatus(200);
        $secondPage->assertJsonCount(10, 'data');
        $this->assertNull($secondPage->json('next_cursor'));

        $firstIds = collect($firstPage->json('data'))->pluck('id');
        $secondIds = collect($secondPage->json('data'))->pluck('id');
        $this->assertCount(0, $firstIds->intersect($secondIds));
    }

    public function test_users_index_invalid_cursor_returns_422(): void
    {
        $response = $this->getJson('/api/users?cursor=not-a-real-cursor', $this->apiKeyHeader());

        $response->assertStatus(422);
    }

    public function test_players_index_paginates_and_next_cursor_becomes_null_at_end(): void
    {
        AnonymousUser::factory()->count(110)->create(['user_id' => null]);

        $firstPage = $this->getJson('/api/players', $this->apiKeyHeader());

        $firstPage->assertStatus(200);
        $firstPage->assertJsonCount(100, 'data');
        $this->assertNotNull($firstPage->json('next_cursor'));

        $secondPage = $this->getJson('/api/players?cursor='.$firstPage->json('next_cursor'), $this->apiKeyHeader());

        $secondPage->assertStatus(200);
        $secondPage->assertJsonCount(10, 'data');
        $this->assertNull($secondPage->json('next_cursor'));

        $firstIds = collect($firstPage->json('data'))->pluck('id');
        $secondIds = collect($secondPage->json('data'))->pluck('id');
        $this->assertCount(0, $firstIds->intersect($secondIds));
    }

    public function test_players_index_invalid_cursor_returns_422(): void
    {
        $response = $this->getJson('/api/players?cursor=not-a-real-cursor', $this->apiKeyHeader());

        $response->assertStatus(422);
    }

    public function test_users_and_players_pagination_are_independent(): void
    {
        User::factory()->count(5)->create();
        AnonymousUser::factory()->count(5)->create(['user_id' => null]);

        $usersResponse = $this->getJson('/api/users', $this->apiKeyHeader());
        $playersResponse = $this->getJson('/api/players', $this->apiKeyHeader());

        $usersResponse->assertStatus(200);
        $playersResponse->assertStatus(200);
        $this->assertCount(5, $usersResponse->json('data'));
        $this->assertCount(5, $playersResponse->json('data'));
    }
}
