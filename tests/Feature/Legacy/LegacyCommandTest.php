<?php

namespace Tests\Feature\Legacy;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Proves that the legacy "plants:send-reminders" command is auto-registered
 * by Kernel::commands() but will throw a QueryException the moment it runs
 * because it queries the Plant model against a table that doesn't exist.
 *
 * This is distinct from the active "casa:send-digest" command (SendDailyDigest),
 * which uses TrackableItem and is safe to run.
 */
class LegacyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_command_is_auto_registered_by_kernel(): void
    {
        // Kernel::commands() calls $this->load(__DIR__.'/Commands'), which
        // auto-registers every file in that directory — including SendPlantReminders.
        // This confirms the command exists in the artisan command list.
        $this->assertArrayHasKey(
            'plants:send-reminders',
            Artisan::all(),
            'plants:send-reminders is auto-loaded from the Commands directory.'
        );
    }

    public function test_active_digest_command_is_also_registered(): void
    {
        // Sanity check: the command we actually want to run is registered too.
        $this->assertArrayHasKey('casa:send-digest', Artisan::all());
    }

    public function test_running_legacy_command_throws_because_plants_table_does_not_exist(): void
    {
        // SendPlantReminders::handle() calls Plant::all() on line 16.
        // Since the "plants" table doesn't exist, this throws immediately.
        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/plants/i');

        Artisan::call('plants:send-reminders');
    }

    public function test_legacy_command_uses_wrong_env_var_name(): void
    {
        // SendPlantReminders reads SLACK_WEBHOOK_URL / services.slack.webhook_url.
        // The active SendDailyDigest reads SLACK_PLANTS_WEBHOOK / services.slack.plants_webhook.
        // Even if the plants table existed, the legacy command would silently use
        // a different (undefined) webhook. We verify the config key is not set.
        $this->assertNull(
            config('services.slack.webhook_url'),
            'services.slack.webhook_url (used by legacy command) is undefined; ' .
            'the active webhook is at services.slack.plants_webhook.'
        );
    }
}
