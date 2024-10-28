<?php

namespace App\Services;

use App\Models\Bike;
use App\Models\Category;
use App\Models\Event;
use App\Models\LapTime;
use App\Models\Race;
use App\Models\Track;
use Illuminate\Support\Facades\Log;

class XmlDataService
{
    public function processXmlFile($filePath): void
    {
        Log::channel('seek_and_stock_process')->info('Process ' . $filePath);

        $xml = simplexml_load_string(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', file_get_contents($filePath)));
        $content = $xml->Results;

        if(!isset($content->Race2)) {
            Log::channel('seek_and_stock_process')->warning('This file is not from a race2');
            return;
        }

        // Recherche d'event correspondant à ces résultats de course
        $event = Event::where('starting_date_timestamp', '<', (int)$content->Event->Date)
            ->where('ending_date_timestamp', '>', (int)$content->Event->Date)
            ->first();

        if(empty($event)) {
            Log::channel('seek_and_stock_process')->warning('Event not found for this race (race date: ' . date('Y-m-d H:i:s', (int)$content->Event->Date) . ' )');
            return;
        }

        $track = Track::firstOrCreate(['label' => $content->Track->Name, 'length' => $content->Track->Length]);

        $race = Race::Create(['date_timestamp' => $content->Event->Date, 'track_id' => $track->id, 'event_id' => $event->id]);

        $entries = [];
        foreach ($content->Entries->Entry as $player) {
            $category = Category::firstOrCreate(['label' => $player->Category]);
            $bike = Bike::firstOrCreate(['label' => $player->Bike, 'category_id' => $category->id]);

            $entries[(string)$player->RaceNum] = [
                'player_guid' => (string)$player->GUID,
                'player_name' => (string)$player->Name,
                'bike_id' => $bike->id,
            ];
        }



        foreach ($content->Race2->FastestLap->Entry as $fastestLap) {
            $entries[(string)$fastestLap->RaceNum]["fastestLapTime"] = (float)$fastestLap->LapTime;
            $entries[(string)$fastestLap->RaceNum]["fastestLapNo"] = (int)$fastestLap->Lap;
        }

        $lapTimes = [];
        foreach ($content->Race2->Analysis->Entry as $playerAnalysis) {
            $playerInfo = $entries[(string)$playerAnalysis->RaceNum];

            foreach ($playerAnalysis->Lap as $lap) {

                $attributes = $lap->attributes();
                $lapNo = (int)$attributes['Num'] + 1;
                $lapTime = (float)$lap->LapTime;
                $lapTimeSector1 = (float)$lap->T1;
                $lapTimeSector2 = (float)$lap->T2 - $lapTimeSector1;
                $lapTimeSector3 = $lapTime - $lapTimeSector2 - $lapTimeSector1;

                $lapTimes[] = [
                    'lap_no' => $lapNo,
                    'lap_time' => $lapTime,
                    'lap_time_sector_1' => $lapTimeSector1,
                    'lap_time_sector_2' => $lapTimeSector2,
                    'lap_time_sector_3' => $lapTimeSector3,
                    'average_speed' => (float)$lap->Speed * 1.60934,
                    'fastest' => ($lapTime === $playerInfo['fastestLapTime'] && $lapNo === $playerInfo['fastestLapNo']),
                    'invalid' => (bool)(int)$lap->Invalid,
                    'race_id' => $race->id,
                    'bike_id' => $playerInfo['bike_id'],
                    'player_guid' => $playerInfo['player_guid'],
                    'player_name' => $playerInfo['player_name'],
                ];
            }
        }

        LapTime::insert($lapTimes);
        Log::channel('seek_and_stock_process')->info('Ending process ' . $filePath);
    }
}

//1699470000 8/11
//1699642800 10/11
//1700074800 15/11
