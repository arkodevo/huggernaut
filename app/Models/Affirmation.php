<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affirmation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'word_sense_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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
