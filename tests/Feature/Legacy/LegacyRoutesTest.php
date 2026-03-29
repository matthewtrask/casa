<?php

namespace Tests\Feature\Legacy;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Proves that PlantController and WateringController are unreachable via HTTP.
 *
 * The original app had a /plants resource route wired to PlantController and a
 * watering POST route wired to WateringController. After the refactor to
 * TrackableItem these controllers were never removed, but their routes were.
 * No HTTP request can reach them.
 */
class LegacyRoutesTest extends TestCase
{
    // ── PlantController routes ────────────────────────────────────────────────

    public function test_plants_index_returns_404(): void
    {
        $this->get('/plants')->assertNotFound();
    }

    public function test_plants_create_returns_404(): void
    {
        $this->get('/plants/create')->assertNotFound();
    }

    public function test_plants_store_returns_404(): void
    {
        $this->post('/plants')->assertNotFound();
    }

    public function test_plants_show_returns_404(): void
    {
        $this->get('/plants/1')->assertNotFound();
    }

    public function test_plants_edit_returns_404(): void
    {
        $this->get('/plants/1/edit')->assertNotFound();
    }

    public function test_plants_update_via_put_returns_404(): void
    {
        $this->put('/plants/1')->assertNotFound();
    }

    public function test_plants_update_via_patch_returns_404(): void
    {
        $this->patch('/plants/1')->assertNotFound();
    }

    public function test_plants_destroy_returns_404(): void
    {
        $this->delete('/plants/1')->assertNotFound();
    }

    // ── WateringController routes ─────────────────────────────────────────────

    public function test_watering_store_returns_404(): void
    {
        // The old app had POST /plants/{plant}/water -> WateringController@store
        $this->post('/plants/1/water')->assertNotFound();
    }

    // ── Named route assertions ────────────────────────────────────────────────

    /**
     * These are the route names PlantController::store/redirect() used.
     * None of them should exist. Note: plants.search / plants.care /
     * plants.identify DO exist — they belong to PlantLookupController (active).
     */
    public function test_legacy_plant_resource_route_names_are_not_registered(): void
    {
        $this->assertFalse(Route::has('plants.index'),   'plants.index should not be registered');
        $this->assertFalse(Route::has('plants.create'),  'plants.create should not be registered');
        $this->assertFalse(Route::has('plants.store'),   'plants.store should not be registered');
        $this->assertFalse(Route::has('plants.show'),    'plants.show should not be registered');
        $this->assertFalse(Route::has('plants.edit'),    'plants.edit should not be registered');
        $this->assertFalse(Route::has('plants.update'),  'plants.update should not be registered');
        $this->assertFalse(Route::has('plants.destroy'), 'plants.destroy should not be registered');
    }

    /**
     * Sanity check: the active PlantLookupController routes (Perenual/PlantNet
     * API proxies) ARE registered. This confirms we're testing the right thing —
     * it's specifically the resource CRUD routes that are gone, not all plant routes.
     */
    public function test_active_plant_lookup_routes_are_still_registered(): void
    {
        $this->assertTrue(Route::has('plants.search'),   'plants.search should exist (PlantLookupController)');
        $this->assertTrue(Route::has('plants.care'),     'plants.care should exist (PlantLookupController)');
        $this->assertTrue(Route::has('plants.identify'), 'plants.identify should exist (PlantLookupController)');
    }
}
