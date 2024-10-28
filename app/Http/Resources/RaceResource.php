<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceResource extends JsonResource
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
            'date_timestamp' => date('Y-m-d H:i:s', $this->date_timestamp),
            'track' => new TrackResource($this->track),
            'lap_times' => LapTimeResource::collection($this->lapTimes),
        ];
    }
}
