<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'user_id', 'content', 'type', 'place_id', 'event_id', 'badge_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function badge(): BelongsTo { return $this->belongsTo(Badge::class); }
    public function images(): HasMany { return $this->hasMany(PostImage::class)->orderBy('sort_order'); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->orderBy('created_at'); }
    public function reactions(): HasMany { return $this->hasMany(Reaction::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }

    // Trending score: reactions + comments weighted by recency
    public function getTrendingScoreAttribute(): float
    {
        $hoursAge = max(1, now()->diffInHours($this->created_at));
        $engagement = ($this->reactions_count ?? 0) * 2 + ($this->comments_count ?? 0) * 3;
        return $engagement / pow($hoursAge, 1.5);
    }
}
