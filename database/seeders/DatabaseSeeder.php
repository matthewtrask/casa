<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrackableItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        // Sample plants
        TrackableItem::create([
            'name' => 'Monstera Deliciosa',
            'species' => 'Monstera deliciosa',
            'location' => 'Living Room',
            'action_frequency_days' => 7,
            'category' => 'plant',
            'sunlight_needs' => 'medium',
            'notes' => 'Keep away from direct sunlight, prefers humid environment',
        ]);

        TrackableItem::create([
            'name' => 'Snake Plant',
            'species' => 'Sansevieria trifasciata',
            'location' => 'Bedroom',
            'action_frequency_days' => 14,
            'category' => 'plant',
            'sunlight_needs' => 'low',
            'notes' => 'Very drought tolerant, water when soil is completely dry',
        ]);

        TrackableItem::create([
            'name' => 'Pothos',
            'species' => 'Epipremnum aureum',
            'location' => 'Office',
            'action_frequency_days' => 5,
            'category' => 'plant',
            'sunlight_needs' => 'medium',
            'notes' => 'Trailing vine, can climb if given support',
        ]);

        // Sample chores
        TrackableItem::create([
            'name' => 'Vacuum Living Room',
            'location' => 'Living Room',
            'action_frequency_days' => 7,
            'category' => 'chore',
        ]);

        TrackableItem::create([
            'name' => 'Clean Bathrooms',
            'location' => 'House',
            'action_frequency_days' => 7,
            'category' => 'chore',
        ]);

        // Sample maintenance
        TrackableItem::create([
            'name' => 'HVAC Filter',
            'location' => 'Utility Room',
            'action_frequency_days' => 90,
            'category' => 'maintenance',
            'notes' => 'Replace with MERV-11 filter',
        ]);
    }
}
