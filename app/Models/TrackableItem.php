<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TrackableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'location',
        'action_frequency_days',
        'category',
        'sunlight_needs',
        'last_action_at',
        'last_secondary_action_at',
        'notes',
        'image_path',
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
        'last_secondary_action_at' => 'datetime',
    ];

    public function actionLogs(): HasMany
    {
        return $this->hasMany(ActionLog::class);
    }

    /**
     * Check if this item is due for its primary action.
     */
    public function isDue(): bool
    {
        if ($this->last_action_at === null) {
            return true;
        }

        $dueDate = $this->last_action_at->addDays($this->action_frequency_days);
        return $dueDate->isPast() || $dueDate->isToday();
    }

    /**
     * Get the number of days overdue (negative if not overdue).
     */
    public function getDaysOverdue(): int
    {
        if ($this->last_action_at === null) {
            return 999;
        }

        $dueDate = $this->last_action_at->addDays($this->action_frequency_days);
        return now()->diffInDays($dueDate, false);
    }

    /**
     * Get status text for display.
     */
    public function getStatusAttribute(): string
    {
        if ($this->last_action_at === null) {
            return "Never {$this->getActionPastTense()} — needs attention now";
        }

        if (!$this->isDue()) {
            $nextDue = $this->last_action_at->addDays($this->action_frequency_days);
            return 'Next due: ' . $nextDue->format('M j');
        }

        $daysOverdue = $this->getDaysOverdue();
        if ($daysOverdue <= 1) {
            return "Due today";
        }
        return "Overdue by {$daysOverdue} days";
    }

    /**
     * Get CSS class for status indicator.
     */
    public function getStatusCssClass(): string
    {
        if (!$this->isDue()) {
            return 'status-ok';
        }

        $daysOverdue = $this->getDaysOverdue();
        if ($daysOverdue > 3) {
            return 'status-critical';
        }

        return 'status-warning';
    }

    /**
     * Get the action label based on category.
     */
    public function getDueLabel(): string
    {
        return match ($this->category) {
            'plant' => 'Water',
            'chore' => 'Complete',
            'maintenance' => 'Service',
            'pet' => 'Feed/Care',
            'other' => 'Complete',
            default => 'Action',
        };
    }

    /**
     * Get the past-tense action label for display (e.g. "watered", "completed").
     */
    public function getActionPastTense(): string
    {
        return match ($this->category) {
            'plant'       => 'watered',
            'chore'       => 'completed',
            'maintenance' => 'serviced',
            'pet'         => 'fed/cared for',
            default       => 'actioned',
        };
    }

    /**
     * Get the emoji for this category.
     */
    public function getCategoryEmoji(): string
    {
        return match ($this->category) {
            'plant' => '🌿',
            'chore' => '🧹',
            'maintenance' => '🔧',
            'pet' => '🐾',
            'other' => '📋',
            default => '📌',
        };
    }

    /**
     * Category helper methods.
     */
    public function isPlant(): bool
    {
        return $this->category === 'plant';
    }

    public function isChore(): bool
    {
        return $this->category === 'chore';
    }

    public function isMaintenance(): bool
    {
        return $this->category === 'maintenance';
    }

    public function isPet(): bool
    {
        return $this->category === 'pet';
    }

    public function isOther(): bool
    {
        return $this->category === 'other';
    }
}
