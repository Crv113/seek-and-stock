<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    /**
     * @var false|mixed|string
     */
    protected $fillable = ['name', 'length', 'image'];

    public function getImageAttribute(): ?string
    {
        return $this->attributes['image'] ? asset('storage/'.$this->attributes['image']) : null;
    }
}
