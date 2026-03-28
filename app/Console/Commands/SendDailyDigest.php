<?php

namespace App\Console\Commands;

use App\Models\TrackableItem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;

class SendDailyDigest extends Command
{
    protected $signature   = 'casa:send-digest';
    protected $description = 'Post a plant watering digest to the #plants Slack channel';

    public function handle(): int
    {
        $webhookUrl = config('services.slack.plants_webhook');

        if (! $webhookUrl) {
            $this->error('SLACK_PLANTS_WEBHOOK is not configured.');
            return 1;
        }

        // Load all plants
        $plants = TrackableItem::where('category', 'plant')->get();

        if ($plants->isEmpty()) {
            $this->info('No plants in the database — nothing to send.');
            return 0;
        }

        // Bucket plants into urgency groups
        $overdue   = collect(); // past their watering date
        $dueToday  = collect(); // due exactly today
        $upcomingSoon = collect(); // due in the next 2 days
        $allGood   = collect(); // fine for now

        foreach ($plants as $plant) {
            if ($plant->last_action_at === null) {
                // Never watered — treat as overdue from day 1
                $overdue->push($plant);
                continue;
            }

            $nextDue   = $plant->last_action_at->copy()->addDays($plant->action_frequency_days);
            $daysUntil = (int) now()->startOfDay()->diffInDays($nextDue->startOfDay(), false);
            // Negative = overdue, 0 = due today, 1–2 = coming up, else = fine

            if ($daysUntil < 0) {
                $overdue->push($plant);
            } elseif ($daysUntil === 0) {
                $dueToday->push($plant);
            } elseif ($daysUntil <= 2) {
                $upcomingSoon->push($plant);
            } else {
                $allGood->push($plant);
            }
        }

        $actionNeeded = $overdue->count() + $dueToday->count();

        if ($actionNeeded === 0 && $upcomingSoon->isEmpty()) {
            $this->info('All plants are happy — skipping Slack notification.');
            return 0;
        }

        // Build the Slack message
        $blocks = $this->buildBlocks($overdue, $dueToday, $upcomingSoon, $allGood);

        try {
            $client = new Client();
            $response = $client->post($webhookUrl, ['json' => ['blocks' => $blocks]]);

            if ($response->getStatusCode() === 200) {
                $this->info("Slack digest sent! ({$actionNeeded} plants need water)");
                return 0;
            }

            $this->error('Slack returned a non-200 response.');
            return 1;
        } catch (\Exception $e) {
            $this->error('Failed to post to Slack: ' . $e->getMessage());
            return 1;
        }
    }

    private function buildBlocks($overdue, $dueToday, $upcomingSoon, $allGood): array
    {
        $today  = now()->format('l, F j');
        $total  = $overdue->count() + $dueToday->count();
        $blocks = [];

        // Header
        $blocks[] = [
            'type' => 'header',
            'text' => [
                'type'  => 'plain_text',
                'text'  => '🌿 Plant Care · ' . $today,
                'emoji' => true,
            ],
        ];

        // Summary context line
        $summaryParts = [];
        if ($overdue->isNotEmpty())  $summaryParts[] = $overdue->count()  . ' overdue';
        if ($dueToday->isNotEmpty()) $summaryParts[] = $dueToday->count() . ' due today';
        if ($upcomingSoon->isNotEmpty()) $summaryParts[] = $upcomingSoon->count() . ' coming up';

        $summaryText = $total > 0
            ? '💧 ' . implode(' · ', $summaryParts) . ' — time to get watering!'
            : '✅ All plants are watered. A few to keep an eye on soon.';

        $blocks[] = [
            'type' => 'context',
            'elements' => [[
                'type' => 'mrkdwn',
                'text' => $summaryText,
            ]],
        ];

        $blocks[] = ['type' => 'divider'];

        // Overdue plants — needs immediate attention
        if ($overdue->isNotEmpty()) {
            $lines = [];
            foreach ($overdue as $plant) {
                if ($plant->last_action_at === null) {
                    $lines[] = "*{$plant->name}* ({$plant->location}) — never watered :rotating_light:";
                } else {
                    $nextDue  = $plant->last_action_at->copy()->addDays($plant->action_frequency_days);
                    $daysAgo  = (int) now()->startOfDay()->diffInDays($nextDue->startOfDay(), false) * -1;
                    $dayWord  = $daysAgo === 1 ? 'day' : 'days';
                    $lines[]  = "*{$plant->name}* ({$plant->location}) — overdue by {$daysAgo} {$dayWord}";
                }
            }

            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => ":red_circle: *Needs water now*\n" . implode("\n", $lines),
                ],
            ];
        }

        // Due today
        if ($dueToday->isNotEmpty()) {
            $lines = [];
            foreach ($dueToday as $plant) {
                $lines[] = "*{$plant->name}* ({$plant->location}) — every {$plant->action_frequency_days} days";
            }

            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => ":large_blue_circle: *Due today*\n" . implode("\n", $lines),
                ],
            ];
        }

        // Coming up soon
        if ($upcomingSoon->isNotEmpty()) {
            $lines = [];
            foreach ($upcomingSoon as $plant) {
                $nextDue  = $plant->last_action_at->copy()->addDays($plant->action_frequency_days);
                $daysUntil = (int) now()->startOfDay()->diffInDays($nextDue->startOfDay(), false);
                $when     = $daysUntil === 1 ? 'tomorrow' : 'in ' . $daysUntil . ' days';
                $lines[]  = "*{$plant->name}* ({$plant->location}) — due {$when}";
            }

            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => ":white_circle: *Coming up soon*\n" . implode("\n", $lines),
                ],
            ];
        }

        // All-good summary (just a count, not noisy)
        if ($allGood->isNotEmpty()) {
            $blocks[] = [
                'type' => 'context',
                'elements' => [[
                    'type' => 'mrkdwn',
                    'text' => ":seedling: {$allGood->count()} " . ($allGood->count() === 1 ? 'plant is' : 'plants are') . ' all good.',
                ]],
            ];
        }

        $blocks[] = ['type' => 'divider'];

        // Footer
        $blocks[] = [
            'type' => 'context',
            'elements' => [[
                'type' => 'mrkdwn',
                'text' => 'Sent by Casa 🏠 · Runs daily at 8 AM',
            ]],
        ];

        return $blocks;
    }
}
