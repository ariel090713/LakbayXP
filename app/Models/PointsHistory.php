<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsHistory extends Model
{
    protected $table = 'points_history';

    protected $fillable = [
        'user_id', 'amount', 'source', 'description',
        'badge_id', 'event_id', 'reward_id', 'granted_by', 'balance_after',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function badge(): BelongsTo { return $this->belongsTo(Badge::class); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function reward(): BelongsTo { return $this->belongsTo(Reward::class); }
    public function grantedBy(): BelongsTo { return $this->belongsTo(User::class, 'granted_by'); }
}
