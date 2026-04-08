<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_path',
        'category',
        'criteria_type',
        'criteria_value',
        'is_active',
        'points',
        'xp_reward',
    ];

    protected $appends = ['icon_url'];

    protected function casts(): array
    {
        return [
            'criteria_value' => 'array',
            'is_active' => 'boolean',
            'points' => 'integer',
            'xp_reward' => 'integer',
        ];
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? \Storage::disk('s3')->url($this->icon_path) : null;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('awarded_at');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'badge_event');
    }
}
