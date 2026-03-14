<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserBadge extends Pivot
{
    protected $table = 'user_badges';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'notified_at',
    ];

    protected $casts = [
        'earned_at'    => 'datetime',
        'notified_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
