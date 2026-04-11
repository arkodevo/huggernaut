<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrammarPatternExampleTranslation extends Model
{
    protected $fillable = ['grammar_pattern_example_id', 'language_id', 'translation_text'];

    public function example(): BelongsTo
    {
        return $this->belongsTo(GrammarPatternExample::class, 'grammar_pattern_example_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
