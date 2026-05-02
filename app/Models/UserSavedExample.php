<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// User-authored sentences — NOT saves of existing word_sense_examples.
// These are original sentences the student writes, optionally AI-verified.
// The Chinese source lives on `chinese_text`; translations (including the
// English rendering) live in the normalized user_saved_example_translations
// table, keyed by language_id. Use ->englishTranslation or
// ->translationFor('en') instead of the retired english_text column.
class UserSavedExample extends Model
{
    protected $table = 'user_saved_examples';

    protected $fillable = [
        'user_id',
        'word_sense_id',
        'word_object_id',
        'chinese_text',
        'original_chinese_text',
        'ai_verified',
        'ai_feedback',
        'source_type',
        'assessed_level',
        'assessed_mastery',
        'mastery_guidance',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'ai_verified' => 'boolean',
            'is_public'   => 'boolean',
        ];
    }

    // Invariant: an unverified writing is never public. Enforced at the model
    // layer so any code path (controller, importer, future seeder, queued job)
    // that tries to persist ai_verified=false with is_public=true gets clamped.
    // The controller and frontend are aligned on this rule today; this hook
    // is defense in depth for tomorrow.
    protected static function booted(): void
    {
        static::saving(function (UserSavedExample $example) {
            if (! $example->ai_verified) {
                $example->is_public = false;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }

    public function wordObject(): BelongsTo
    {
        return $this->belongsTo(WordObject::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(UserSavedExampleTranslation::class);
    }

    /**
     * English translation text — canonical accessor.
     * Reads from the normalized user_saved_example_translations table.
     * Use this everywhere instead of the retired $english_text column.
     */
    public function getEnglishTranslationAttribute(): ?string
    {
        return $this->translationFor('en');
    }

    public function translationFor(string|int $langCodeOrId): ?string
    {
        $langId = is_int($langCodeOrId)
            ? $langCodeOrId
            : Language::where('code', $langCodeOrId)->value('id');

        if (! $langId) return null;

        // Use loaded relation when available to avoid N+1.
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $langId)?->translation_text;
        }

        return $this->translations()
            ->where('language_id', $langId)
            ->value('translation_text');
    }

    // Grammar patterns 師父 identified in this writing during critique.
    public function grammarPatterns(): BelongsToMany
    {
        return $this->belongsToMany(
                GrammarPattern::class,
                'user_saved_example_grammar_patterns'
            )
            ->withPivot('status', 'note')
            ->withTimestamps();
    }
}
