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

class EventResultsCursorPaginationTest extends TestCase
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

    private function createBike(): Bike
    {
        $category = Category::factory()->create();

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

    public function test_event_results_paginate_and_next_cursor_becomes_null_at_end(): void
    {
        $event = $this->createEvent();
        $bike = $this->createBike();

        for ($i = 0; $i < 110; $i++) {
            $user = User::factory()->create(['guid' => "user-guid-{$i}"]);
            $this->createLapTime($event, $bike, $user->guid, 60000 + $i);
        }

        $firstPage = $this->getJson("/api/events/{$event->id}/results", $this->apiKeyHeader());

        $firstPage->assertStatus(200);
        $firstPage->assertJsonCount(100, 'data');
        $this->assertNotNull($firstPage->json('next_cursor'));

        $secondPage = $this->getJson(
            "/api/events/{$event->id}/results?cursor=".$firstPage->json('next_cursor'),
            $this->apiKeyHeader()
        );

        $secondPage->assertStatus(200);
        $secondPage->assertJsonCount(10, 'data');
        $this->assertNull($secondPage->json('next_cursor'));

        $firstIds = collect($firstPage->json('data'))->pluck('id');
        $secondIds = collect($secondPage->json('data'))->pluck('id');
        $this->assertCount(0, $firstIds->intersect($secondIds));
    }

    public function test_event_results_tie_break_keeps_oldest_lap_time_id_first(): void
    {
        $event = $this->createEvent();
        $bike = $this->createBike();

        $userA = User::factory()->create(['guid' => 'tie-guid-a']);
        $lapA = $this->createLapTime($event, $bike, $userA->guid, 50000);

        $userB = User::factory()->create(['guid' => 'tie-guid-b']);
        $lapB = $this->createLapTime($event, $bike, $userB->guid, 50000);

        $userC = User::factory()->create(['guid' => 'tie-guid-c']);
        $this->createLapTime($event, $bike, $userC->guid, 60000);

        $response = $this->getJson("/api/events/{$event->id}/results", $this->apiKeyHeader());

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertSame([$lapA->id, $lapB->id], $ids->take(2)->values()->all());
    }

    public function test_event_results_invalid_cursor_returns_422(): void
    {
        $event = $this->createEvent();

        $response = $this->getJson(
            "/api/events/{$event->id}/results?cursor=not-a-real-cursor",
            $this->apiKeyHeader()
        );

        $response->assertStatus(422);
    }

    public function test_event_results_excludes_laptimes_without_known_player(): void
    {
        $event = $this->createEvent();
        $bike = $this->createBike();

        $user = User::factory()->create(['guid' => 'known-guid']);
        $known = $this->createLapTime($event, $bike, $user->guid, 40000);

        $this->createLapTime($event, $bike, 'ghost-guid-not-registered', 10000);

        $response = $this->getJson("/api/events/{$event->id}/results", $this->apiKeyHeader());

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertCount(1, $ids);
        $this->assertTrue($ids->contains($known->id));
    }

    public function test_event_results_includes_anonymous_users_laptimes(): void
    {
        $event = $this->createEvent();
        $bike = $this->createBike();

        $anon = AnonymousUser::factory()->create(['guid' => 'anon-guid-included']);
        $lap = $this->createLapTime($event, $bike, $anon->guid, 45000);

        $response = $this->getJson("/api/events/{$event->id}/results", $this->apiKeyHeader());

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($lap->id));
    }
}
