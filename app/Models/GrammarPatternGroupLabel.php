<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrammarPatternGroupLabel extends Model
{
    protected $fillable = ['grammar_pattern_group_id', 'language_id', 'name', 'description'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(GrammarPatternGroup::class, 'grammar_pattern_group_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
