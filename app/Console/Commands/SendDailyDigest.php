<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\TrackableItem;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class SendDailyDigest extends Command
{
    protected $signature   = 'casa:send-digest';
    protected $description = 'Post a daily home digest to Slack, routing each category to its own channel';

    private array $categoryConfig = [
        'plant'       => ['emoji' => '🌿', 'label' => 'Plants',      'overdue_icon' => ':rotating_light:', 'due_icon' => ':large_blue_circle:'],
        'chore'       => ['emoji' => '🧹', 'label' => 'Chores',      'overdue_icon' => ':red_circle:',      'due_icon' => ':large_yellow_circle:'],
        'maintenance' => ['emoji' => '🔧', 'label' => 'Maintenance', 'overdue_icon' => ':red_circle:',      'due_icon' => ':large_yellow_circle:'],
        'pet'         => ['emoji' => '🐾', 'label' => 'Pets',        'overdue_icon' => ':red_circle:',      'due_icon' => ':large_blue_circle:'],
        'other'       => ['emoji' => '📌', 'label' => 'Other',       'overdue_icon' => ':red_circle:',      'due_icon' => ':large_yellow_circle:'],
    ];

    public function handle(): int
    {
        $token          = config('services.slack.bot_token');
        $defaultChannel = Setting::get('slack_channel_default', '#all-casa');

        if (! $token) {
            $this->error('SLACK_API_TOKEN is not set in .env.');
            return 1;
        }

        $allItems = TrackableItem::all();

        if ($allItems->isEmpty()) {
            $this->info('No items in the database — nothing to send.');
            return 0;
        }

        // Classify every item by urgency
        $byCategory = [];

        foreach ($allItems as $item) {
            $category = $item->category;

            if (! isset($byCategory[$category])) {
                $byCategory[$category] = ['overdue' => collect(), 'dueToday' => collect(), 'upcomingSoon' => collect(), 'allGood' => collect()];
            }

            if ($item->last_action_at === null) {
                $byCategory[$category]['overdue']->push($item);
                continue;
            }

            $nextDue   = $item->last_action_at->copy()->addDays($item->action_frequency_days);
            $daysUntil = (int) now()->startOfDay()->diffInDays($nextDue->startOfDay(), false);

            if ($daysUntil < 0)       $byCategory[$category]['overdue']->push($item);
            elseif ($daysUntil === 0) $byCategory[$category]['dueToday']->push($item);
            elseif ($daysUntil <= 2)  $byCategory[$category]['upcomingSoon']->push($item);
            else                      $byCategory[$category]['allGood']->push($item);
        }

        // Group categories by their resolved channel so we batch
        // categories sharing the same channel into one message
        $channelGroups = [];

        foreach (array_keys($byCategory) as $category) {
            $channel = Setting::get("slack_channel_{$category}") ?? $defaultChannel;
            $channelGroups[$channel][] = $category;
        }

        $client     = new Client();
        $errorCount = 0;

        foreach ($channelGroups as $channel => $categories) {
            $overdue      = collect();
            $dueToday     = collect();
            $upcomingSoon = collect();
            $allGood      = collect();

            foreach ($categories as $cat) {
                $overdue->push(...$byCategory[$cat]['overdue']);
                $dueToday->push(...$byCategory[$cat]['dueToday']);
                $upcomingSoon->push(...$byCategory[$cat]['upcomingSoon']);
                $allGood->push(...$byCategory[$cat]['allGood']);
            }

            $actionNeeded = $overdue->count() + $dueToday->count();

            if ($actionNeeded === 0 && $upcomingSoon->isEmpty()) {
                $label = implode(', ', array_map(fn($c) => $this->categoryConfig[$c]['label'] ?? $c, $categories));
                $this->info("Skipping [{$label}] — everything up to date.");
                continue;
            }

            $blocks = $this->buildBlocks($overdue, $dueToday, $upcomingSoon, $allGood, $categories);

            try {
                $response = $client->post('https://slack.com/api/chat.postMessage', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type'  => 'application/json; charset=utf-8',
                    ],
                    'json' => [
                        'channel' => $channel,
                        'blocks'  => $blocks,
                    ],
                ]);

                $body = json_decode($response->getBody(), true);
                $label = implode(', ', array_map(fn($c) => $this->categoryConfig[$c]['label'] ?? $c, $categories));

                if ($body['ok'] ?? false) {
                    $this->info("✓ Sent [{$label}] digest to {$channel} ({$actionNeeded} items need attention)");
                } else {
                    $this->error("Slack error for {$channel}: " . ($body['error'] ?? 'unknown'));
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to post to {$channel}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        return $errorCount > 0 ? 1 : 0;
    }

    private function buildBlocks($overdue, $dueToday, $upcomingSoon, $allGood, array $categories): array
    {
        $today    = now()->format('l, F j');
        $total    = $overdue->count() + $dueToday->count();
        $isMulti  = count($categories) > 1;
        $heading  = $isMulti ? '🏠 Casa Daily Digest' : ($this->categoryConfig[$categories[0]]['emoji'] . ' ' . ($this->categoryConfig[$categories[0]]['label'] ?? $categories[0]) . ' Digest');
        $blocks   = [];

        $blocks[] = [
            'type' => 'header',
            'text' => ['type' => 'plain_text', 'text' => $heading . ' · ' . $today, 'emoji' => true],
        ];

        $summaryParts = [];
        if ($overdue->isNotEmpty())      $summaryParts[] = $overdue->count() . ' overdue';
        if ($dueToday->isNotEmpty())     $summaryParts[] = $dueToday->count() . ' due today';
        if ($upcomingSoon->isNotEmpty()) $summaryParts[] = $upcomingSoon->count() . ' coming up soon';

        $summaryText = $total > 0
            ? '⚠️ ' . implode(' · ', $summaryParts)
            : '✅ All caught up. A few things coming up soon.';

        $blocks[] = ['type' => 'context', 'elements' => [['type' => 'mrkdwn', 'text' => $summaryText]]];
        $blocks[] = ['type' => 'divider'];

        if ($overdue->isNotEmpty()) {
            $lines = [];
            foreach ($overdue->groupBy('category') as $category => $items) {
                $cfg = $this->categoryConfig[$category] ?? $this->categoryConfig['other'];
                foreach ($items as $item) {
                    if ($item->last_action_at === null) {
                        $lines[] = "{$cfg['emoji']} *{$item->name}* ({$item->location}) — never actioned {$cfg['overdue_icon']}";
                    } else {
                        $nextDue = $item->last_action_at->copy()->addDays($item->action_frequency_days);
                        $daysAgo = abs((int) now()->startOfDay()->diffInDays($nextDue->startOfDay(), false));
                        $word    = $daysAgo === 1 ? 'day' : 'days';
                        $lines[] = "{$cfg['emoji']} *{$item->name}* ({$item->location}) — overdue by {$daysAgo} {$word} {$cfg['overdue_icon']}";
                    }
                }
            }
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "*Needs attention now*\n" . implode("\n", $lines)]];
        }

        if ($dueToday->isNotEmpty()) {
            $lines = [];
            foreach ($dueToday->groupBy('category') as $category => $items) {
                $cfg = $this->categoryConfig[$category] ?? $this->categoryConfig['other'];
                foreach ($items as $item) {
                    $lines[] = "{$cfg['due_icon']} {$cfg['emoji']} *{$item->name}* ({$item->location})";
                }
            }
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "*Due today*\n" . implode("\n", $lines)]];
        }

        if ($upcomingSoon->isNotEmpty()) {
            $lines = [];
            foreach ($upcomingSoon->groupBy('category') as $category => $items) {
                $cfg = $this->categoryConfig[$category] ?? $this->categoryConfig['other'];
                foreach ($items as $item) {
                    $nextDue   = $item->last_action_at->copy()->addDays($item->action_frequency_days);
                    $daysUntil = (int) now()->startOfDay()->diffInDays($nextDue->startOfDay(), false);
                    $when      = $daysUntil === 1 ? 'tomorrow' : "in {$daysUntil} days";
                    $lines[]   = ":white_circle: {$cfg['emoji']} *{$item->name}* ({$item->location}) — due {$when}";
                }
            }
            $blocks[] = ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => "*Coming up soon*\n" . implode("\n", $lines)]];
        }

        if ($allGood->isNotEmpty()) {
            $blocks[] = ['type' => 'context', 'elements' => [['type' => 'mrkdwn', 'text' => ":seedling: {$allGood->count()} " . ($allGood->count() === 1 ? 'item is' : 'items are') . ' all good.']]];
        }

        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'context', 'elements' => [['type' => 'mrkdwn', 'text' => 'Sent by Casa 🏠 · Runs daily at 8 AM']]];

        return $blocks;
    }
}
