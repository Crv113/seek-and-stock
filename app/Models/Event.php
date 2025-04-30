<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'image', 'starting_date', 'ending_date', 'track_id'];

    public function getImageAttribute(): ?string
    {
        return $this->attributes['image'] ? asset('storage/' . $this->attributes['image']) : null;
    }

    public function lapTimes(): HasMany
    {
        return $this->hasMany(LapTime::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user')->withTimestamps();
    }

    public function bestLapTime(): HasOne
    {
        return $this->hasOne(LapTime::class, 'event_id', 'id')
            ->whereIn('player_guid', function ($query) {
                $query->select('guid')->from('users');
            })
            ->orderBy('lap_time')
            ->orderBy('id');
    }
}
