<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'content', 'parent_id'];

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function parent(): BelongsTo { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(Comment::class, 'parent_id'); }
    public function reactions(): HasMany { return $this->hasMany(CommentReaction::class); }

    public function getReactionCountsAttribute(): array
    {
        return $this->reactions()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
