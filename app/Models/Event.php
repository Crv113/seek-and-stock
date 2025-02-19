<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image', 'starting_date', 'ending_date', 'track_id'];

    public function getImageAttribute(): ?string
    {
        return $this->attributes['image'] ? asset('storage/' . $this->attributes['image']) : null;
    }

    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }

    public function track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
