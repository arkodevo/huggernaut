<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// User-authored sentences — NOT saves of existing word_sense_examples.
// These are original sentences the student writes, optionally AI-verified.
class UserSavedExample extends Model
{
    protected $table = 'user_saved_examples';

    protected $fillable = [
        'user_id',
        'word_sense_id',
        'word_object_id',
        'chinese_text',
        'english_text',
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
