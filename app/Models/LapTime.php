<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LapTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'lap_time',
        'lap_time_sector_1',
        'lap_time_sector_2',
        'lap_time_sector_3',
        'average_speed',
        'event_id',
        'bike_id',
        'player_guid',
        'player_name'
        ];

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }
}
