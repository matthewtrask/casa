<?php

namespace Tests\Feature\Legacy;

use App\Models\Plant;
use App\Models\WateringLog;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves that querying the legacy Plant and WateringLog models throws a
 * QueryException at runtime because neither "plants" nor "watering_logs"
 * tables are created by any migration.
 *
 * Any code path that reaches these models — SendPlantReminders, DatabaseSeeder,
 * PlantController, WateringController — will fail with the same error.
 */
class LegacyModelsQueryTest extends TestCase
{
    use RefreshDatabase;

    // ── Plant model ───────────────────────────────────────────────────────────

    public function test_plant_all_throws_because_plants_table_does_not_exist(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/plants/i');

        Plant::all();
    }

    public function test_plant_create_throws_because_plants_table_does_not_exist(): void
    {
        $this->expectException(QueryException::class);

        Plant::create([
            'name'                => 'Monstera',
            'species'             => 'Monstera deliciosa',
            'location'            => 'Living Room',
            'water_frequency_days' => 7,
            'sunlight_needs'      => 'medium',
        ]);
    }

    public function test_plant_find_throws_because_plants_table_does_not_exist(): void
    {
        $this->expectException(QueryException::class);

        Plant::find(1);
    }

    // ── WateringLog model ─────────────────────────────────────────────────────

    public function test_watering_log_all_throws_because_watering_logs_table_does_not_exist(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/watering_logs/i');

        WateringLog::all();
    }

    public function test_watering_log_create_throws_because_watering_logs_table_does_not_exist(): void
    {
        $this->expectException(QueryException::class);

        WateringLog::create([
            'plant_id'   => 1,
            'watered_by' => 'Matt',
        ]);
    }

    // ── Contrast: active models work fine ────────────────────────────────────

    public function test_trackable_item_can_be_queried_without_error(): void
    {
        $result = \App\Models\TrackableItem::all();

        $this->assertCount(0, $result);
    }

    public function test_action_log_can_be_queried_without_error(): void
    {
        $result = \App\Models\ActionLog::all();

        $this->assertCount(0, $result);
    }
}
