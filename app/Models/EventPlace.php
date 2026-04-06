<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPlace extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id', 'place_id', 'custom_place_name', 'custom_place_location',
        'day_number', 'sort_order', 'activity', 'time_slot', 'notes',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get the display name — system place name or custom name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->place?->name ?? $this->custom_place_name ?? 'Unknown';
    }

    /**
     * Check if this is a system place (earns XP/points) or custom.
     */
    public function isSystemPlace(): bool
    {
        return $this->place_id !== null;
    }
}
