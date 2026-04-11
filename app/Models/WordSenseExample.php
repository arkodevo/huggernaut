<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Example sentences attached to a sense (and optionally a specific definition).
// source: default · student · ai_generated · community
// is_suppressed: global editorial flag — hides from all users.
class WordSenseExample extends Model
{
    protected $fillable = [
        'word_sense_id',
        'definition_id',
        'chinese_text',
        'english_text',
        'source',
        'user_id',
        'ai_verified',
        'is_public',
        'is_suppressed',
        'theme',
    ];

    protected function casts(): array
    {
        return [
            'ai_verified'   => 'boolean',
            'is_public'     => 'boolean',
            'is_suppressed' => 'boolean',
        ];
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WordSenseDefinition::class, 'definition_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(WordSenseExampleTranslation::class);
    }

    public function grammarPatterns(): BelongsToMany
    {
        return $this->belongsToMany(
            GrammarPattern::class,
            'word_sense_example_grammar_patterns',
            'word_sense_example_id',
            'grammar_pattern_id'
        )->withTimestamps();
    }

    public function scopeVisible($query)
    {
        return $query->where('is_public', true)->where('is_suppressed', false);
    }
}
