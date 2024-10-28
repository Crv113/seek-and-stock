<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LapTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player_guid' => $this->player_guid,
            'player_name' => $this->player_name,
            'lap_no' => $this->lap_no,
            'fastest' => $this->fastest,
            'invalid' => $this->invalid,
            'average_speed' => number_format($this->average_speed, 3),
            'lap_time' => number_format($this->lap_time, 3),
            'lap_time_sector_1' => number_format($this->lap_time_sector_1, 3),
            'lap_time_sector_2' => number_format($this->lap_time_sector_2, 3),
            'lap_time_sector_3' => number_format($this->lap_time_sector_3, 3),
            'bike' => new BikeResource($this->bike),
        ];
    }
}
