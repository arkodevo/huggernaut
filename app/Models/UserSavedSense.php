<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Saves are at the sense level, not the word_object level.
// Composite PK: (user_id, word_sense_id). Use updateOrCreate() for upserts.
class UserSavedSense extends Model
{
    protected $table = 'user_saved_senses';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'word_sense_id',
        'personal_note',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
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
