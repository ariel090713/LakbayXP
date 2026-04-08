<?php

namespace App\Models;

use App\Enums\PlaceCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'region',
        'province',
        'latitude',
        'longitude',
        'cover_image_path',
        'category_fields',
        'is_active',
        'xp_reward',
        'points_reward',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => PlaceCategory::class,
            'category_fields' => 'array',
            'is_active' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected $appends = ['cover_image_url'];

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image_path ? \Storage::disk('s3')->url($this->cover_image_path) : null;
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PlaceImage::class)->orderBy('sort_order');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(PlaceMeta::class);
    }

    public function getMeta(string $key, $default = null): ?string
    {
        return $this->meta->firstWhere('meta_key', $key)?->meta_value ?? $default;
    }

    public function setMeta(string $key, mixed $value): void
    {
        // Cast arrays/numbers to string
        if (is_array($value)) {
            $value = implode(', ', $value);
        } elseif ($value !== null) {
            $value = (string) $value;
        }

        PlaceMeta::updateOrCreate(
            ['place_id' => $this->id, 'meta_key' => $key],
            ['meta_value' => $value]
        );
    }

    public function syncMeta(array $metaData): void
    {
        foreach ($metaData as $key => $value) {
            if ($value !== null && $value !== '') {
                $this->setMeta($key, $value);
            }
        }
    }

    public function unlockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'place_unlocks')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
