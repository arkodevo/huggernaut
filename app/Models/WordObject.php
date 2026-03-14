<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// The pure orthographic identity — 行 as a character, separate from any pronunciation or meaning.
// smart_id is the stable Unicode codepoint slug, e.g. u884c for 行.
class WordObject extends Model
{
    protected $fillable = [
        'smart_id',
        'traditional',
        'simplified',
        'radical_id',
        'strokes_trad',
        'strokes_simp',
        'structure',
        'status',
    ];

    public function radical(): BelongsTo
    {
        return $this->belongsTo(Radical::class);
    }

    public function pronunciations(): HasMany
    {
        return $this->hasMany(WordPronunciation::class);
    }

    public function senses(): HasMany
    {
        return $this->hasMany(WordSense::class);
    }

    // Word senses that list this word_object as a collocation partner.
    public function collocatingWordSenses(): BelongsToMany
    {
        return $this->belongsToMany(
            WordSense::class,
            'word_sense_collocations',
            'collocation_word_object_id',
            'word_sense_id'
        )->using(WordSenseCollocation::class)->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
