<?php

namespace App\Console\Commands;

use App\Models\Plant;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class SendPlantReminders extends Command
{
    protected $signature = 'plants:send-reminders';
    protected $description = 'Send Slack notification for plants that need watering today';

    public function handle(): int
    {
        $plantsNeedingWater = Plant::all()->filter(fn ($plant) => $plant->isDueForWater());

        if ($plantsNeedingWater->isEmpty()) {
            $this->info('No plants need watering today.');
            return 0;
        }

        $webhookUrl = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');

        if (!$webhookUrl) {
            $this->error('SLACK_WEBHOOK_URL not configured.');
            return 1;
        }

        $plantList = $plantsNeedingWater
            ->map(fn ($plant) => "• {$plant->name} ({$plant->location})")
            ->join("\n");

        $payload = [
            'text' => '🌿 Good morning! Time to water these plants:',
            'attachments' => [
                [
                    'color' => 'good',
                    'text' => $plantList,
                    'footer' => 'Casa - Plant Care Tracker',
                    'ts' => time(),
                ]
            ]
        ];

        try {
            $client = new Client();
            $response = $client->post($webhookUrl, [
                'json' => $payload,
            ]);

            if ($response->getStatusCode() === 200) {
                $this->info('Slack notification sent successfully!');
                $this->info('Plants watering due: ' . $plantsNeedingWater->count());
                return 0;
            }
        } catch (\Exception $e) {
            $this->error('Failed to send Slack notification: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
