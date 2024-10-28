<?php

namespace Database\Factories;

use App\Models\LapTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LapTimeFactory extends Factory
{
    protected $model = LapTime::class;

    public function definition(): array
    {
        return [
            'race_id' => $this->faker->randomDigitNotNull(),
            'player_guid' => $this->faker->word(),
            'player_name' => $this->faker->word(),
            'bike_id' => $this->faker->randomDigitNotNull(),
            'lap_no' => $this->faker->randomDigitNotNull(),
            'fastest' => $this->faker->boolean(),
            'invalid' => $this->faker->boolean(),
            'lap_time' => $this->faker->randomFloat(),
            'lap_time_sector_1' => $this->faker->randomFloat(),
            'lap_time_sector_2' => $this->faker->randomFloat(),
            'lap_time_sector_3' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
