<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'discord_id',
        'discord_username',
        'discord_global_name',
        'discord_avatar',
        'discord_locale',
        'guid',
        'name',
        'email',
        'password',
        'guid'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($user) {
            $user->assignRole('user');

            if ($user->email === config('custom.super_admin_email')) {
                $user->syncRoles(['admin']);
            }
        });
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user')->withTimestamps();
    }
}
