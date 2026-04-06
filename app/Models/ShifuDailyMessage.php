<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShifuDailyMessage extends Model
{
    protected $fillable = [
        'user_id',
        'message_date',
        'persona_slug',
        'message_text',
        'context_snapshot',
        'feedback',
        'feedback_at',
    ];

    protected $casts = [
        'message_date'     => 'date',
        'context_snapshot'  => 'array',
        'feedback_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
