<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrammarPatternLabel extends Model
{
    protected $fillable = ['grammar_pattern_id', 'language_id', 'name', 'short_description'];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(GrammarPattern::class, 'grammar_pattern_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
