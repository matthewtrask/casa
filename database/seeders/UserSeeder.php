<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Matt
        User::firstOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'name'     => 'Matt',
                'password' => Hash::make(env('SEED_PASSWORD_MATT', 'change-me-immediately')),
            ]
        );

        // Add your wife's email below
        User::firstOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'name'     => 'Kieran',   // ← update this
                'password' => Hash::make(env('SEED_PASSWORD_PARTNER', 'change-me-immediately')),
            ]
        );

        $this->command->info('Users created. Set real passwords with: php artisan tinker');
        $this->command->info("  \$user = User::find(1); \$user->password = bcrypt('newpassword'); \$user->save();");
    }
}
