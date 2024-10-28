<?php

namespace Database\Factories;

use App\Models\Race;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RaceFactory extends Factory
{
    protected $model = Race::class;

    public function definition(): array
    {
        return [
            'date_timestamp' => $this->faker->randomDigitNotNull(),
            'track_id' => $this->faker->randomDigitNotNull(),
            'event_id' => $this->faker->randomDigitNotNull(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
