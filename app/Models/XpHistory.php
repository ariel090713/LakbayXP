<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpHistory extends Model
{
    protected $table = 'xp_history';

    protected $fillable = [
        'user_id', 'amount', 'source', 'category', 'description',
        'place_id', 'badge_id', 'event_id', 'granted_by', 'balance_after',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
    public function badge(): BelongsTo { return $this->belongsTo(Badge::class); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function grantedBy(): BelongsTo { return $this->belongsTo(User::class, 'granted_by'); }
}
