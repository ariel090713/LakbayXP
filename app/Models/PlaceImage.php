<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PlaceImage extends Model
{
    protected $fillable = ['place_id', 'image_path', 'image_source', 'caption', 'is_cover', 'sort_order'];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
    }

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('s3')->url($this->image_path) : null;
    }
}
