<?php

namespace Tests\Feature;

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

class EventResultsCategoryFilterTest extends TestCase
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

    public function test_category_filter_recalculates_ranking_within_category(): void
    {
        $event = $this->createEvent();

        $bikeA = $this->createBike('CategoryA');
        $bikeB = $this->createBike('CategoryB');

        $player1 = User::factory()->create(['guid' => 'player-1']);
        $player2 = User::factory()->create(['guid' => 'player-2']);

        $player1FastOverallButSlowInA = $this->createLapTime($event, $bikeA, $player1->guid, 40000);
        $this->createLapTime($event, $bikeB, $player1->guid, 20000);

        $player2OnlyInA = $this->createLapTime($event, $bikeA, $player2->guid, 45000);

        $response = $this->getJson(
            "/api/events/{$event->id}/results?category_id={$bikeA->category_id}",
            $this->apiKeyHeader()
        );

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id')->values()->all();

        $this->assertSame([$player1FastOverallButSlowInA->id, $player2OnlyInA->id], $ids);
    }

    public function test_category_filter_tie_break_keeps_oldest_lap_time_id_first_within_category(): void
    {
        $event = $this->createEvent();

        $bikeA = $this->createBike('CategoryA');
        $bikeB = $this->createBike('CategoryB');

        $playerX = User::factory()->create(['guid' => 'player-x']);
        $playerY = User::factory()->create(['guid' => 'player-y']);
        $playerZ = User::factory()->create(['guid' => 'player-z']);

        $lapX = $this->createLapTime($event, $bikeA, $playerX->guid, 50000);
        $lapY = $this->createLapTime($event, $bikeA, $playerY->guid, 50000);

        $this->createLapTime($event, $bikeB, $playerZ->guid, 10000);

        $response = $this->getJson(
            "/api/events/{$event->id}/results?category_id={$bikeA->category_id}",
            $this->apiKeyHeader()
        );

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id')->values()->all();

        $this->assertSame([$lapX->id, $lapY->id], $ids);
    }

    public function test_category_filter_with_non_numeric_category_id_returns_422(): void
    {
        $event = $this->createEvent();

        $response = $this->getJson(
            "/api/events/{$event->id}/results?category_id=not-a-number",
            $this->apiKeyHeader()
        );

        $response->assertStatus(422);
    }

    public function test_category_filter_with_nonexistent_category_id_returns_422(): void
    {
        $event = $this->createEvent();

        $response = $this->getJson(
            "/api/events/{$event->id}/results?category_id=999999",
            $this->apiKeyHeader()
        );

        $response->assertStatus(422);
    }

    public function test_overall_results_without_category_filter_are_unchanged(): void
    {
        $event = $this->createEvent();

        $bikeA = $this->createBike('CategoryA');
        $bikeB = $this->createBike('CategoryB');

        $player1 = User::factory()->create(['guid' => 'player-1']);
        $player2 = User::factory()->create(['guid' => 'player-2']);

        $this->createLapTime($event, $bikeA, $player1->guid, 40000);
        $fastestOverallForPlayer1 = $this->createLapTime($event, $bikeB, $player1->guid, 20000);

        $fastestForPlayer2 = $this->createLapTime($event, $bikeA, $player2->guid, 45000);

        $response = $this->getJson("/api/events/{$event->id}/results", $this->apiKeyHeader());

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id')->values()->all();

        $this->assertSame([$fastestOverallForPlayer1->id, $fastestForPlayer2->id], $ids);
    }
}
