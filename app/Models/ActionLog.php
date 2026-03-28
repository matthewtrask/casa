<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'trackable_item_id',
        'action_type',
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function trackableItem(): BelongsTo
    {
        return $this->belongsTo(TrackableItem::class);
    }
}
