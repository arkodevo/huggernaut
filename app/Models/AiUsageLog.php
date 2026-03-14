<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tracks AI credit consumption per user per request (造句 Workshop).
// request_type: feedback · generation
class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'word_sense_id',
        'request_type',
        'credits_used',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }
}
