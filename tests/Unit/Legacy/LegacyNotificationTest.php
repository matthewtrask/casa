<?php

namespace Tests\Unit\Legacy;

use App\Notifications\PlantCareNotification;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Proves that PlantCareNotification is non-functional dead code.
 *
 * It declares ['slack'] as its notification channel, which requires the
 * laravel/slack-notification-channel package. That package is not in
 * composer.json. Additionally the toSlack() method instantiates
 * \Illuminate\Notifications\Messages\SlackMessage, a class that was removed
 * from the Laravel core in v10 and never added as a standalone dependency.
 */
class LegacyNotificationTest extends TestCase
{
    public function test_slack_message_class_does_not_exist_in_this_installation(): void
    {
        // \Illuminate\Notifications\Messages\SlackMessage was removed from Laravel
        // core in v10+. It now lives in laravel/slack-notification-channel.
        // That package is not installed, so the class does not exist.
        $this->assertFalse(
            class_exists(\Illuminate\Notifications\Messages\SlackMessage::class),
            'SlackMessage class should not exist — laravel/slack-notification-channel is not installed.'
        );
    }

    public function test_plant_care_notification_routes_to_slack_channel(): void
    {
        // The notification is correctly constructed — it accepts a Collection.
        $notification = new PlantCareNotification(collect());

        // via() returns ['slack'], pointing at the non-existent channel driver.
        $this->assertSame(['slack'], $notification->via(null));
    }

    public function test_calling_to_slack_throws_because_slack_message_class_is_missing(): void
    {
        // Pass a proper Plant instance (no DB hit — just instantiation) so the
        // $plant->name mapping succeeds and execution reaches the SlackMessage line.
        $plant = new \App\Models\Plant(['name' => 'Monstera', 'location' => 'Living Room']);
        $notification = new PlantCareNotification(collect([$plant]));

        // \Illuminate\Notifications\Messages\SlackMessage was removed from Laravel
        // core in v10+. Instantiating it throws an Error: Class not found.
        $this->expectException(\Error::class);
        $this->expectExceptionMessageMatches('/SlackMessage/');

        $notification->toSlack(null);
    }

    public function test_to_slack_returns_null_for_empty_collection(): void
    {
        // When the collection is empty, toSlack() returns null before reaching
        // the SlackMessage instantiation — so this specific path doesn't crash.
        $notification = new PlantCareNotification(collect());

        $result = $notification->toSlack(null);

        $this->assertNull($result);
    }
}
