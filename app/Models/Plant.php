<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'location',
        'water_frequency_days',
        'sunlight_needs',
        'last_watered_at',
        'last_fertilized_at',
        'notes',
        'image_path',
    ];

    protected $casts = [
        'last_watered_at' => 'datetime',
        'last_fertilized_at' => 'datetime',
    ];

    public function wateringLogs(): HasMany
    {
        return $this->hasMany(WateringLog::class);
    }

    public function isDueForWater(): bool
    {
        if ($this->last_watered_at === null) {
            return true;
        }

        $dueDate = $this->last_watered_at->addDays($this->water_frequency_days);
        return $dueDate->isPast() || $dueDate->isToday();
    }

    public function isDueForFertilizer(): bool
    {
        if ($this->last_fertilized_at === null) {
            return true;
        }

        // Assume fertilizer every 30 days
        $dueDate = $this->last_fertilized_at->addDays(30);
        return $dueDate->isPast() || $dueDate->isToday();
    }

    public function getWaterStatusAttribute(): string
    {
        if (!$this->isDueForWater()) {
            $nextDue = $this->last_watered_at->addDays($this->water_frequency_days);
            return 'OK - Next due: ' . $nextDue->format('M d');
        }

        $daysOverdue = now()->diffInDays($this->last_watered_at->addDays($this->water_frequency_days), false);
        return "OVERDUE by {$daysOverdue} days";
    }

    public function getWaterStatusCssClass(): string
    {
        if (!$this->isDueForWater()) {
            return 'status-ok';
        }

        $daysOverdue = now()->diffInDays($this->last_watered_at->addDays($this->water_frequency_days), false);
        if ($daysOverdue > 3) {
            return 'status-critical';
        }

        return 'status-warning';
    }
}
