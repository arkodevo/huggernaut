<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordSenseExampleTranslation extends Model
{
    protected $fillable = [
        'word_sense_example_id',
        'language_id',
        'translation_text',
    ];

    public function example(): BelongsTo
    {
        return $this->belongsTo(WordSenseExample::class, 'word_sense_example_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
