<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\PlaceCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'place_id',
        'title',
        'slug',
        'description',
        'category',
        'event_date',
        'end_date',
        'meeting_place',
        'meeting_time',
        'fee',
        'max_slots',
        'difficulty',
        'requirements',
        'status',
        'auto_approve_bookings',
    ];

    protected function casts(): array
    {
        return [
            'category' => PlaceCategory::class,
            'status' => EventStatus::class,
            'event_date' => 'date',
            'end_date' => 'date',
            'fee' => 'decimal:2',
            'requirements' => 'array',
            'auto_approve_bookings' => 'boolean',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function itinerary(): HasMany
    {
        return $this->hasMany(EventPlace::class)->orderBy('day_number')->orderBy('sort_order');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(EventRule::class)->orderBy('sort_order');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_event');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class);
    }

    public function availableSlots(): int
    {
        return $this->max_slots - $this->bookings()
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->count();
    }
}
