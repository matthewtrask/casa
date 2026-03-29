<?php

namespace Tests\Unit\Legacy;

use App\Models\Plant;
use App\Models\TrackableItem;
use App\Models\WateringLog;
use Tests\TestCase;

/**
 * Proves that the legacy model definitions reference the wrong tables and
 * wrong column names — without touching the database at all.
 *
 * This is a pure unit test: it instantiates the models and inspects their
 * metadata. No query is executed. The mismatches here explain exactly why
 * every database interaction with these models fails at runtime.
 */
class LegacyModelDefinitionsTest extends TestCase
{
    // ── Plant model ───────────────────────────────────────────────────────────

    public function test_plant_model_targets_plants_table_which_does_not_exist(): void
    {
        $plant = new Plant();

        // Laravel derives the table name from the class name by convention.
        // Plant → "plants". No migration creates this table.
        $this->assertSame('plants', $plant->getTable());
    }

    public function test_plant_fillable_contains_legacy_column_names(): void
    {
        $plant = new Plant();
        $fillable = $plant->getFillable();

        // These columns exist only in the old design.
        // TrackableItem uses action_frequency_days and last_action_at instead.
        $this->assertContains('water_frequency_days', $fillable);
        $this->assertContains('last_watered_at',      $fillable);
        $this->assertContains('last_fertilized_at',   $fillable);
    }

    public function test_plant_fillable_does_not_contain_current_column_names(): void
    {
        $plant = new Plant();
        $fillable = $plant->getFillable();

        // The current schema uses these names. Plant knows nothing about them.
        $this->assertNotContains('action_frequency_days', $fillable);
        $this->assertNotContains('last_action_at',        $fillable);
        $this->assertNotContains('category',              $fillable);
    }

    // ── WateringLog model ─────────────────────────────────────────────────────

    public function test_watering_log_model_targets_watering_logs_table_which_does_not_exist(): void
    {
        $log = new WateringLog();

        // WateringLog → "watering_logs". The migration named create_watering_logs_table.php
        // actually creates "action_logs", not "watering_logs".
        $this->assertSame('watering_logs', $log->getTable());
    }

    public function test_watering_log_fillable_references_plant_id_not_trackable_item_id(): void
    {
        $log = new WateringLog();
        $fillable = $log->getFillable();

        // WateringLog.plant_id references the non-existent Plant model.
        // ActionLog uses trackable_item_id instead.
        $this->assertContains('plant_id', $fillable);
        $this->assertNotContains('trackable_item_id', $fillable);
    }

    // ── Contrast: TrackableItem uses the correct definitions ──────────────────

    public function test_trackable_item_targets_trackable_items_table(): void
    {
        $item = new TrackableItem();

        $this->assertSame('trackable_items', $item->getTable());
    }

    public function test_trackable_item_fillable_uses_current_column_names(): void
    {
        $item = new TrackableItem();
        $fillable = $item->getFillable();

        $this->assertContains('action_frequency_days', $fillable);
        $this->assertContains('last_action_at',        $fillable);
        $this->assertContains('category',              $fillable);
    }
}
