<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LapTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'lap_no',
        'lap_time',
        'lap_time_sector_1',
        'lap_time_sector_2',
        'lap_time_sector_3',
        'average_speed',
        'fastest',
        'invalid',
        'race_id',
        'bike_id',
        'player_guid',
        'player_name'
        ];

    public function race():BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(Bike::class);
    }
}
