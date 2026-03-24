<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Saves are at the word level (word_object).
// Composite PK: (user_id, word_object_id). Use updateOrCreate() for upserts.
class UserSavedWord extends Model
{
    protected $table = 'user_saved_words';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'word_object_id',
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

    public function wordObject(): BelongsTo
    {
        return $this->belongsTo(WordObject::class);
    }
}
