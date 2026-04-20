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
        'source',
        'user_id',
        'ai_verified',
        'is_public',
        'is_suppressed',
        'theme',
        'has_audio',
    ];

    protected function casts(): array
    {
        return [
            'ai_verified'   => 'boolean',
            'is_public'     => 'boolean',
            'is_suppressed' => 'boolean',
            'has_audio'     => 'array',
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

    /**
     * English translation text — canonical accessor.
     * Reads from the normalized word_sense_example_translations table.
     * Use this everywhere instead of the retired $english_text column.
     *
     * For multi-language display, use $example->translationFor($langId)
     * or iterate $example->translations directly.
     */
    public function getEnglishTranslationAttribute(): ?string
    {
        return $this->translationFor('en');
    }

    public function translationFor(string|int $langCodeOrId): ?string
    {
        $langId = is_int($langCodeOrId)
            ? $langCodeOrId
            : \App\Models\Language::where('code', $langCodeOrId)->value('id');

        if (! $langId) return null;

        // If translations already loaded, use the in-memory collection —
        // avoids an N+1 explosion on list views.
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $langId)?->translation_text;
        }

        return $this->translations()
            ->where('language_id', $langId)
            ->value('translation_text');
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
