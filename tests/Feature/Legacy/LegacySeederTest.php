<?php

namespace Tests\Feature\Legacy;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves that DatabaseSeeder crashes because it calls Plant::create() with
 * columns that don't exist in the current schema.
 *
 * This is important because the deploy script runs "php artisan migrate --force"
 * but NOT the seeder. If someone runs "php artisan db:seed" manually on a fresh
 * deployment, the app will need at least the UserSeeder to create usable accounts.
 * Running the full DatabaseSeeder will fail.
 */
class LegacySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_throws_because_it_calls_plant_create(): void
    {
        // DatabaseSeeder::run() calls $this->call(UserSeeder::class) first (fine),
        // then three Plant::create() calls. The third line of run() that hits
        // Plant::create() will throw because "plants" table does not exist.
        $this->expectException(QueryException::class);

        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_seeder_runs_cleanly_when_called_directly(): void
    {
        // UserSeeder is safe — it only creates User records in the "users" table,
        // which does exist. Calling it directly (skipping DatabaseSeeder) works
        // and is what should be run on first deploy.
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_user_seeder_creates_expected_accounts(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'mjftrask@gmail.com']);
    }
}
