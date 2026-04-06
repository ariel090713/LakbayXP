<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceMeta extends Model
{
    protected $table = 'place_meta';

    protected $fillable = ['place_id', 'meta_key', 'meta_value'];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
