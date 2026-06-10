<?php

namespace Database\Factories;

use App\Models\AnonymousUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnonymousUserFactory extends Factory
{
    protected $model = AnonymousUser::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid(),
            'player_name' => $this->faker->name(),
            'user_id' => null,
        ];
    }
}
