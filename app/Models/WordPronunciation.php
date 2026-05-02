<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// One row per character, per pronunciation system, per reading.
// 行 has two readings: háng (trade/profession) and xíng (walk/travel).
class WordPronunciation extends Model
{
    protected $fillable = [
        'word_object_id',
        'pronunciation_system_id',
        'pronunciation_text',
        'is_primary',
        'dialect_region',
        'audio_file',
        'has_audio',
        'audio_text_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'has_audio'  => 'array',
        ];
    }

    public function wordObject(): BelongsTo
    {
        return $this->belongsTo(WordObject::class);
    }

    public function pronunciationSystem(): BelongsTo
    {
        return $this->belongsTo(PronunciationSystem::class);
    }

    public function wordSenses(): HasMany
    {
        return $this->hasMany(WordSense::class, 'pronunciation_id');
    }
}
