<?php

namespace Tests\Feature;

use App\Models\AnonymousUser;
use App\Models\Bike;
use App\Models\Category;
use App\Models\Event;
use App\Models\LapTime;
use App\Models\Track;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class EventCategoriesTest extends TestCase
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

    private function createEvent(): Event
    {
        $track = Track::factory()->create(['image' => 'track.jpg']);

        return Event::factory()->create([
            'track_id' => $track->id,
            'starting_date' => now()->subDay(),
            'ending_date' => now()->subHour(),
        ]);
    }

    private function createBike(string $categoryName): Bike
    {
        $category = Category::factory()->create(['name' => $categoryName]);

        return Bike::factory()->create(['category_id' => $category->id]);
    }

    private function createLapTime(Event $event, Bike $bike, string $guid, int $lapTime): LapTime
    {
        return LapTime::factory()->create([
            'event_id' => $event->id,
            'bike_id' => $bike->id,
            'player_guid' => $guid,
            'lap_time' => $lapTime,
            'lap_time_sector_1' => intdiv($lapTime, 3),
            'lap_time_sector_2' => intdiv($lapTime, 3),
            'lap_time_sector_3' => $lapTime - 2 * intdiv($lapTime, 3),
        ]);
    }

    public function test_event_categories_returns_distinct_categories_sorted_by_name(): void
    {
        $event = $this->createEvent();

        $bikeZ = $this->createBike('Zeta');
        $bikeA = $this->createBike('Alpha');
        $bikeM = $this->createBike('Mid');

        $user1 = User::factory()->create(['guid' => 'user-1']);
        $user2 = User::factory()->create(['guid' => 'user-2']);
        $user3 = User::factory()->create(['guid' => 'user-3']);
        $user4 = User::factory()->create(['guid' => 'user-4']);

        $this->createLapTime($event, $bikeZ, $user1->guid, 40000);
        $this->createLapTime($event, $bikeA, $user2->guid, 41000);
        $this->createLapTime($event, $bikeM, $user3->guid, 42000);
        $this->createLapTime($event, $bikeZ, $user4->guid, 43000);

        $response = $this->getJson("/api/events/{$event->id}/categories", $this->apiKeyHeader());

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertArrayNotHasKey('data', $response->json());
        $names = collect($response->json())->pluck('name')->values()->all();

        $this->assertSame(['Alpha', 'Mid', 'Zeta'], $names);
    }

    public function test_event_categories_returns_empty_array_when_no_lap_times(): void
    {
        $event = $this->createEvent();

        $response = $this->getJson("/api/events/{$event->id}/categories", $this->apiKeyHeader());

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    public function test_event_categories_returns_404_when_event_does_not_exist(): void
    {
        $response = $this->getJson('/api/events/999999/categories', $this->apiKeyHeader());

        $response->assertStatus(404);
    }

    public function test_event_categories_excludes_categories_only_reached_by_unknown_player(): void
    {
        $event = $this->createEvent();

        $bikeKnown = $this->createBike('Known');
        $bikeGhost = $this->createBike('GhostOnly');

        $user = User::factory()->create(['guid' => 'known-guid']);
        $this->createLapTime($event, $bikeKnown, $user->guid, 40000);
        $this->createLapTime($event, $bikeGhost, 'ghost-guid-not-registered', 10000);

        $response = $this->getJson("/api/events/{$event->id}/categories", $this->apiKeyHeader());

        $response->assertStatus(200);
        $names = collect($response->json())->pluck('name')->values()->all();

        $this->assertSame(['Known'], $names);
    }

    public function test_event_categories_includes_categories_reached_by_anonymous_users(): void
    {
        $event = $this->createEvent();
        $bike = $this->createBike('AnonCategory');

        $anon = AnonymousUser::factory()->create(['guid' => 'anon-guid-included']);
        $this->createLapTime($event, $bike, $anon->guid, 45000);

        $response = $this->getJson("/api/events/{$event->id}/categories", $this->apiKeyHeader());

        $response->assertStatus(200);
        $names = collect($response->json())->pluck('name')->values()->all();

        $this->assertSame(['AnonCategory'], $names);
    }
}
