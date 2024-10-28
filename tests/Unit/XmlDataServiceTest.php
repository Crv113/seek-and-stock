<?php

namespace Tests\Unit;

use App\Models\Bike;
use App\Models\Category;
use App\Models\Event;
use App\Models\LapTime;
use App\Models\Track;
use App\Services\XmlDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XmlDataServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_processes_a_valid_xml_file()
    {
        config()->set('logging.channels.seek_and_stock_process', [
            'driver' => 'single',
            'path' => storage_path('logs/seek_and_stock_process_test.log'),
            'level' => 'debug',
        ]);

        $filePath = base_path('tests/Fixtures/valid_race.xml');

        $xmlDataService = new XmlDataService();

        $event = Event::factory()->create([
            'starting_date_timestamp' => 1699470000,
            'ending_date_timestamp' => 1700074800,
        ]);

        $xmlDataService->processXmlFile($filePath);

        $this->assertCount(30, LapTime::all());
        $this->assertCount(2, Category::all());
        $this->assertCount(2, Bike::all());

        $track = Track::first();

        $this->assertDatabaseHas('events', ['name' => $event->name]);
        $this->assertDatabaseHas('races', ['date_timestamp' => 1699566980, 'event_id' => $event->id, 'track_id' => $track->id]);
        $this->assertDatabaseHas('tracks', ['label' => '2023 ARL SX PreSeason', 'length' => 793.0770870000]);
        $this->assertDatabaseHas('lap_times', [
            'player_guid' => 'FF741',
            'player_name' => 'Harry',
            'lap_no' => 10,
            'fastest' => true,
            'invalid' => false,
            'average_speed'=>round(14.775308 * 1.60934, 10), //On arrondi le resultat car en base le champ est un decimal(20,10)
            'lap_time'=> 53.675842,
        ]);

        $logContent = file_get_contents(storage_path('logs/seek_and_stock_process_test.log'));
        $this->assertStringContainsString('Process ' . $filePath, $logContent);
        $this->assertStringContainsString('Ending process ' . $filePath, $logContent);

    }

    public function test_it_log_warning_if_no_event_for_this_race()
    {
        config()->set('logging.channels.seek_and_stock_process', [
            'driver' => 'single',
            'path' => storage_path('logs/seek_and_stock_process_test.log'),
            'level' => 'debug',
        ]);

        $filePath = base_path('tests/Fixtures/valid_race.xml');

        $xmlDataService = new XmlDataService();

        $xmlDataService->processXmlFile($filePath);

        $logContent = file_get_contents(storage_path('logs/seek_and_stock_process_test.log'));
        $this->assertStringContainsString('Event not found for this race (race date: 2023-11-09 21:56:20 )', $logContent);

        file_put_contents(storage_path('logs/seek_and_stock_process_test.log'), '');
    }

    public function test_it_log_warning_if_no_race2_in_this_file()
    {
        config()->set('logging.channels.seek_and_stock_process', [
            'driver' => 'single',
            'path' => storage_path('logs/seek_and_stock_process_test.log'),
            'level' => 'debug',
        ]);

        $filePath = base_path('tests/Fixtures/no_race2_results.xml');

        $xmlDataService = new XmlDataService();

        $xmlDataService->processXmlFile($filePath);

        $logContent = file_get_contents(storage_path('logs/seek_and_stock_process_test.log'));
        $this->assertStringContainsString('This file is not from a race2', $logContent);

        file_put_contents(storage_path('logs/seek_and_stock_process_test.log'), '');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $logPath = storage_path('logs/seek_and_stock_process_test.log');
        if (file_exists($logPath)) {
            unlink($logPath);
        }
    }
}
//1699470000 8/11
//1699642800 10/11
//1700074800 15/11
