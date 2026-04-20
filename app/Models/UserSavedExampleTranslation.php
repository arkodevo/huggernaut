<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Per-language translation row for a user-authored writing.
// Keyed by (user_saved_example_id, language_id). Multilingual-native.
class UserSavedExampleTranslation extends Model
{
    protected $fillable = [
        'user_saved_example_id',
        'language_id',
        'translation_text',
    ];

    public function userSavedExample(): BelongsTo
    {
        return $this->belongsTo(UserSavedExample::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
