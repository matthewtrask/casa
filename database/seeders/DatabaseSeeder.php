<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        // Create sample plants
        Plant::create([
            'name' => 'Monstera Deliciosa',
            'species' => 'Monstera deliciosa',
            'location' => 'Living Room',
            'water_frequency_days' => 7,
            'sunlight_needs' => 'medium',
            'notes' => 'Keep away from direct sunlight, prefers humid environment',
        ]);

        Plant::create([
            'name' => 'Snake Plant',
            'species' => 'Sansevieria trifasciata',
            'location' => 'Bedroom',
            'water_frequency_days' => 14,
            'sunlight_needs' => 'low',
            'notes' => 'Very drought tolerant, water when soil is completely dry',
        ]);

        Plant::create([
            'name' => 'Pothos',
            'species' => 'Epipremnum aureum',
            'location' => 'Office',
            'water_frequency_days' => 5,
            'sunlight_needs' => 'medium',
            'notes' => 'Trailing vine, can climb if given support',
        ]);
    }
}
