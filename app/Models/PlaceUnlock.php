<?php

namespace App\Models;

use App\Enums\UnlockMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceUnlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'unlock_method',
        'proof_photo_path',
        'verified_by',
        'event_id',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unlock_method' => UnlockMethod::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
