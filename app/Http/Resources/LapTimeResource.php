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
            'average_speed' =>$this->average_speed,
            'lap_time' => $this->lap_time,
            'lap_time_sector_1' => $this->lap_time_sector_1,
            'lap_time_sector_2' => $this->lap_time_sector_2,
            'lap_time_sector_3' => $this->lap_time_sector_3,
            'bike' => new BikeResource($this->bike),
        ];
    }
}
