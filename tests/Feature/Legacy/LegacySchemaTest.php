<?php

namespace Tests\Feature\Legacy;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Proves the database schema matches the current TrackableItem design,
 * not the old plant-only schema.
 *
 * The migration files are misleadingly named (create_plants_table.php,
 * create_watering_logs_table.php) but they actually create trackable_items
 * and action_logs. These tests confirm what is and isn't in the database
 * after a fresh migration run.
 */
class LegacySchemaTest extends TestCase
{
    use RefreshDatabase;

    // ── Active tables ─────────────────────────────────────────────────────────

    public function test_trackable_items_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('trackable_items'));
    }

    public function test_action_logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('action_logs'));
    }

    public function test_users_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
    }

    // ── Legacy tables that were never created ─────────────────────────────────

    public function test_plants_table_does_not_exist(): void
    {
        $this->assertFalse(
            Schema::hasTable('plants'),
            'No migration creates a "plants" table — Plant::all() will always throw.'
        );
    }

    public function test_watering_logs_table_does_not_exist(): void
    {
        $this->assertFalse(
            Schema::hasTable('watering_logs'),
            'No migration creates a "watering_logs" table — WateringLog::all() will always throw.'
        );
    }

    // ── Current column names (TrackableItem) ─────────────────────────────────

    public function test_trackable_items_has_current_column_names(): void
    {
        $this->assertTrue(Schema::hasColumn('trackable_items', 'action_frequency_days'));
        $this->assertTrue(Schema::hasColumn('trackable_items', 'last_action_at'));
        $this->assertTrue(Schema::hasColumn('trackable_items', 'category'));
    }

    // ── Legacy column names that never made it into any migration ─────────────

    public function test_trackable_items_does_not_have_legacy_water_frequency_column(): void
    {
        $this->assertFalse(
            Schema::hasColumn('trackable_items', 'water_frequency_days'),
            '"water_frequency_days" is in Plant::$fillable but was never added to trackable_items.'
        );
    }

    public function test_trackable_items_does_not_have_legacy_last_watered_column(): void
    {
        $this->assertFalse(
            Schema::hasColumn('trackable_items', 'last_watered_at'),
            '"last_watered_at" is in Plant::$fillable but was never added to trackable_items.'
        );
    }

    public function test_trackable_items_does_not_have_legacy_last_fertilized_column(): void
    {
        $this->assertFalse(
            Schema::hasColumn('trackable_items', 'last_fertilized_at'),
            '"last_fertilized_at" is in Plant::$fillable but was never added to trackable_items.'
        );
    }
}
