<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// User-authored sentences — NOT saves of existing word_sense_examples.
// These are original sentences the student writes, optionally AI-verified.
class UserSavedExample extends Model
{
    protected $table = 'user_saved_examples';

    protected $fillable = [
        'user_id',
        'word_sense_id',
        'chinese_text',
        'english_text',
        'ai_verified',
        'ai_feedback',
        'source_type',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'ai_verified' => 'boolean',
            'is_public'   => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }
}
