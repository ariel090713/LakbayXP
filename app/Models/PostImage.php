<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostImage extends Model
{
    protected $fillable = ['post_id', 'image_path', 'sort_order'];

    protected $appends = ['image_url'];

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('s3')->url($this->image_path) : null;
    }
}
