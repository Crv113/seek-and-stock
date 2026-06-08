<?php

namespace Tests\Feature;

use App\Models\AnonymousUser;
use App\Models\Event;
use App\Models\Track;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnonymousPlayerTest extends TestCase
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

    private function lapTimePayload(array $overrides = []): array
    {
        $track = Track::factory()->create(['name' => 'TestTrack', 'image' => 'test.jpg']);
        $event = Event::factory()->create([
            'name' => 'TestEvent',
            'track_id' => $track->id,
            'starting_date' => now()->subHour(),
            'ending_date' => now()->addHour(),
        ]);

        return array_merge([
            'event_name' => 'TestEvent',
            'track_name' => 'TestTrack',
            'lap_time' => 95.5,
            'lap_time_sector_1' => 30.0,
            'lap_time_sector_2' => 65.0,
            'bike_name' => 'TestBike',
            'category_name' => 'MX1',
            'player_guid' => 'unknown-guid-abc',
            'player_name' => 'GhostRider',
        ], $overrides);
    }

    public function test_post_laptimes_with_unknown_guid_creates_anonymous_user(): void
    {
        $payload = $this->lapTimePayload(['player_guid' => 'anon-guid-001', 'player_name' => 'AnonPlayer']);

        $response = $this->postJson('/api/laptimes', $payload, $this->apiKeyHeader());

        $response->assertStatus(201);
        $this->assertDatabaseHas('anonymous_users', [
            'guid' => 'anon-guid-001',
            'player_name' => 'AnonPlayer',
        ]);
    }

    public function test_post_laptimes_with_known_guid_does_not_create_anonymous_user(): void
    {
        $user = User::factory()->create(['guid' => 'known-guid-001']);
        $payload = $this->lapTimePayload(['player_guid' => 'known-guid-001', 'player_name' => 'KnownPlayer']);

        $response = $this->postJson('/api/laptimes', $payload, $this->apiKeyHeader());

        $response->assertStatus(201);
        $this->assertDatabaseMissing('anonymous_users', ['guid' => 'known-guid-001']);
    }

    public function test_post_laptimes_twice_same_guid_updates_player_name_without_duplicate(): void
    {
        $track = Track::factory()->create(['name' => 'DupTrack', 'image' => 'dup.jpg']);
        $event = Event::factory()->create([
            'name' => 'DupEvent',
            'track_id' => $track->id,
            'starting_date' => now()->subHour(),
            'ending_date' => now()->addHour(),
        ]);

        $basePayload = [
            'event_name' => 'DupEvent',
            'track_name' => 'DupTrack',
            'lap_time' => 90.0,
            'lap_time_sector_1' => 30.0,
            'lap_time_sector_2' => 60.0,
            'bike_name' => 'BikeDup',
            'category_name' => 'MX2',
            'player_guid' => 'dup-guid-001',
        ];

        $this->postJson('/api/laptimes', array_merge($basePayload, ['player_name' => 'FirstName']), $this->apiKeyHeader());
        $this->postJson('/api/laptimes', array_merge($basePayload, ['player_name' => 'SecondName']), $this->apiKeyHeader());

        $this->assertDatabaseCount('anonymous_users', 1);
        $this->assertDatabaseHas('anonymous_users', [
            'guid' => 'dup-guid-001',
            'player_name' => 'SecondName',
        ]);
    }

    public function test_put_user_with_guid_merges_anonymous_laptimes_and_returns_merged_true(): void
    {
        $user = User::factory()->create(['guid' => null]);
        $anonGuid = 'merge-guid-001';

        AnonymousUser::factory()->create(['guid' => $anonGuid, 'player_name' => 'AnonMerge', 'user_id' => null]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user', ['guid' => $anonGuid]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['merged' => true]);

        $this->assertDatabaseHas('anonymous_users', [
            'guid' => $anonGuid,
            'user_id' => $user->id,
        ]);
    }

    public function test_put_user_with_guid_without_anonymous_user_returns_merged_false(): void
    {
        $user = User::factory()->create(['guid' => null]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user', ['guid' => 'no-anon-guid-here']);

        $response->assertStatus(200);
        $response->assertJsonFragment(['merged' => false]);
    }

    public function test_get_players_index_returns_anonymous_users_without_user_id(): void
    {
        AnonymousUser::factory()->create(['player_name' => 'AnonOne', 'user_id' => null]);
        AnonymousUser::factory()->create(['player_name' => 'AnonTwo', 'user_id' => null]);
        $linked = User::factory()->create(['guid' => null]);
        AnonymousUser::factory()->create(['player_name' => 'Linked', 'user_id' => $linked->id]);

        $response = $this->getJson('/api/players', $this->apiKeyHeader());

        $response->assertStatus(200);
        $data = $response->json();
        $names = collect($data)->pluck('player_name');
        $this->assertTrue($names->contains('AnonOne'));
        $this->assertTrue($names->contains('AnonTwo'));
        $this->assertFalse($names->contains('Linked'));
    }

    public function test_get_players_index_requires_api_key(): void
    {
        $response = $this->getJson('/api/players');

        $response->assertStatus(401);
    }

    public function test_get_player_by_guid_returns_stats(): void
    {
        $guid = 'player-guid-stats';
        $anon = AnonymousUser::factory()->create(['guid' => $guid, 'player_name' => 'StatsPlayer']);

        $response = $this->getJson("/api/players/{$anon->id}", $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $anon->id,
            'player_name' => 'StatsPlayer',
        ]);
        $response->assertJsonStructure(['data' => ['id', 'player_name', 'user_id', 'best_lap_times_by_track']]);
    }

    public function test_get_player_by_unknown_guid_returns_404(): void
    {
        $response = $this->getJson('/api/players/99999', $this->apiKeyHeader());

        $response->assertStatus(404);
    }

    public function test_get_player_requires_api_key(): void
    {
        $anon = AnonymousUser::factory()->create();

        $response = $this->getJson("/api/players/{$anon->id}");

        $response->assertStatus(401);
    }

    public function test_merge_does_not_overwrite_existing_user_id(): void
    {
        $user1 = User::factory()->create(['guid' => null]);
        $user2 = User::factory()->create(['guid' => null]);
        $guid = 'merge-guid-protected';

        AnonymousUser::factory()->create(['guid' => $guid, 'user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->putJson('/api/user', ['guid' => $guid]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['merged' => false]);

        $this->assertDatabaseHas('anonymous_users', [
            'guid' => $guid,
            'user_id' => $user2->id,
        ]);
    }
}
