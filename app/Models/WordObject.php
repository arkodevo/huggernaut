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
        'alignment',
        'subtlex_rank',
        'subtlex_ppm',
        'subtlex_cd',
        'shifu_reviewed_at',
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
