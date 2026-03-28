<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WateringLog;
use App\Models\Plant;

class WateringLogFactory extends Factory
{
    protected $model = WateringLog::class;

    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'watered_by' => $this->faker->name(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
