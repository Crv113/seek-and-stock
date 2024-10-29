<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'starting_date', 'ending_date'];

    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }
}
