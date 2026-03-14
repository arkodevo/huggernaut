<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// POS and definition are inseparable (v1.5 §3C).
// Each row is one {pos, definition} pair in one language for one word sense.
class WordSenseDefinition extends Model
{
    protected $fillable = [
        'word_sense_id',
        'language_id',
        'pos_id',
        'definition_text',
        'formula',
        'usage_note',
        'sort_order',
    ];

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function posLabel(): BelongsTo
    {
        return $this->belongsTo(PosLabel::class, 'pos_id');
    }

    public function examples(): HasMany
    {
        return $this->hasMany(WordSenseExample::class, 'definition_id');
    }
}
