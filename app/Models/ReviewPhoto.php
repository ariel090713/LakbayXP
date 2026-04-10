<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReviewPhoto extends Model
{
    protected $fillable = ['review_id', 'photo_path', 'sort_order'];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('s3')->url($this->photo_path) : null;
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
