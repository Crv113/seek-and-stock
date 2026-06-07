<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnonymousPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'guid' => $this->guid,
            'player_name' => $this->player_name,
            'user_id' => $this->user_id,
            'best_lap_times' => $this->best_lap_times,
        ];
    }
}
