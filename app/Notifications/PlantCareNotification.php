<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PlantCareNotification extends Notification
{
    protected Collection $plantsNeedingWater;

    public function __construct(Collection $plantsNeedingWater)
    {
        $this->plantsNeedingWater = $plantsNeedingWater;
    }

    public function via($notifiable): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        if ($this->plantsNeedingWater->isEmpty()) {
            return null;
        }

        $plantList = $this->plantsNeedingWater
            ->map(fn ($plant) => "• {$plant->name} ({$plant->location})")
            ->join("\n");

        return (new \Illuminate\Notifications\Messages\SlackMessage)
            ->success()
            ->from('Casa')
            ->content("🌿 *Good morning!* Time to water these plants:")
            ->attachment(function ($attachment) use ($plantList) {
                $attachment
                    ->text($plantList)
                    ->footer('Casa - Plant Care Tracker');
            });
    }
}
