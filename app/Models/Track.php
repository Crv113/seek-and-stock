<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use HasFactory;

    /**
     * @var false|mixed|string
     */
    protected $fillable = ['name', 'length', 'image'];

    public function getImageAttribute(): ?string
    {
        return $this->attributes['image'] ? asset('storage/' . $this->attributes['image']) : null;
    }

    public function races():HasMany
    {
        return $this->hasMany(Race::class);
    }
}
