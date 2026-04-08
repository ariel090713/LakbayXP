<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'points_cost',
        'stock',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'points_cost' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? \Storage::disk('s3')->url($this->image_path) : null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function availableStock(): int
    {
        $claimedCount = $this->redemptions()
            ->whereIn('status', ['pending', 'approved', 'claimed'])
            ->count();

        return max(0, $this->stock - $claimedCount);
    }
}
