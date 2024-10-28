<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use HasFactory;
    protected $fillable = ['label', 'length'];

    public function races():HasMany
    {
        return $this->hasMany(Race::class);
    }
}
