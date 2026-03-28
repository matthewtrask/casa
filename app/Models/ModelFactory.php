<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Plant;

class PlantFactory extends Factory
{
    protected $model = Plant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'species' => $this->faker->sentence(2),
            'location' => $this->faker->word(),
            'water_frequency_days' => $this->faker->numberBetween(3, 14),
            'sunlight_needs' => $this->faker->randomElement(['low', 'medium', 'high', 'direct']),
            'notes' => $this->faker->paragraph(),
        ];
    }
}
