<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordSenseNote extends Model
{
    protected $fillable = [
        'word_sense_id',
        'language_id',
        'note_type_id',
        'content',
    ];

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function noteType(): BelongsTo
    {
        return $this->belongsTo(NoteType::class);
    }
}
