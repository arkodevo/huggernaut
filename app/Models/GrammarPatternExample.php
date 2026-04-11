<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrammarPatternExample extends Model
{
    protected $fillable = [
        'grammar_pattern_id',
        'chinese_text',
        'pinyin_text',
        'source',
        'user_id',
        'ai_verified',
        'is_suppressed',
        'sort_order',
    ];

    protected $casts = [
        'ai_verified' => 'boolean',
        'is_suppressed' => 'boolean',
    ];

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(GrammarPattern::class, 'grammar_pattern_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GrammarPatternExampleTranslation::class);
    }
}
