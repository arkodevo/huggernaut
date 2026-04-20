<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// The primary lexical unit. 行 = 5 word_senses across 2 pronunciations.
//
// Single-select spectrum FKs (channel, connotation, sensitivity,
// tocfl_level, hsk_level) are direct FKs to designations for query efficiency.
// semantic_mode retired 2026-04-20 — dimension covers the literal/figurative axis.
//
// Multi-select attributes (register, dimension) live in word_sense_designations pivot.
// Domains live in word_sense_domains pivot (many-to-many, ordered by sort_order, max 4).
class WordSense extends Model
{
    protected $fillable = [
        'word_object_id',
        'pronunciation_id',
        'channel_id',
        'connotation_id',
        'sensitivity_id',
        'intensity',
        'valency',
        'formula',
        'usage_note',
        'learner_traps',
        'tocfl_level_id',
        'hsk_level_id',
        'status',
        'source',
        'alignment',
        'enriched_by',
        'enriched_at',
        'audited_by',
        'audited_at',
    ];

    // ── Parent ────────────────────────────────────────────────────────────────

    public function wordObject(): BelongsTo
    {
        return $this->belongsTo(WordObject::class);
    }

    public function pronunciation(): BelongsTo
    {
        return $this->belongsTo(WordPronunciation::class, 'pronunciation_id');
    }

    // ── Single-select spectrum designation FKs ────────────────────────────────

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'channel_id');
    }

    public function connotation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'connotation_id');
    }

    public function sensitivity(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'sensitivity_id');
    }

    // ── Domains (many-to-many via word_sense_domains pivot) ──────────────────

    /**
     * All domains for this sense (primary + secondary), ordered by sort_order.
     */
    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Designation::class, 'word_sense_domains')
            ->using(WordSenseDomain::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function tocflLevel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'tocfl_level_id');
    }

    public function hskLevel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'hsk_level_id');
    }

    // ── Content ───────────────────────────────────────────────────────────────

    public function definitions(): HasMany
    {
        return $this->hasMany(WordSenseDefinition::class)->orderBy('sort_order');
    }

    public function examples(): HasMany
    {
        return $this->hasMany(WordSenseExample::class);
    }

    // ── Notes (bilingual, per note_type) ─────────────────────────────────────

    public function notes(): HasMany
    {
        return $this->hasMany(WordSenseNote::class);
    }

    // ── Multi-select designations (register, dimension) ───────────────────────

    public function designations(): BelongsToMany
    {
        return $this->belongsToMany(Designation::class, 'word_sense_designations')
            ->using(WordSenseDesignation::class)
            ->withTimestamps();
    }

    // ── POS index ─────────────────────────────────────────────────────────────

    public function posLabels(): BelongsToMany
    {
        return $this->belongsToMany(PosLabel::class, 'word_sense_pos', 'word_sense_id', 'pos_id')
            ->using(WordSensePos::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    // ── Collocations (text-based) ────────────────────────────────────────────

    public function collocations(): HasMany
    {
        return $this->hasMany(WordSenseCollocation::class);
    }

    // ── Relations (synonyms, antonyms, family tree, etc.) ────────────────────

    public function senseRelations(): HasMany
    {
        return $this->hasMany(WordSenseRelation::class, 'word_sense_id');
    }

    // ── User data ─────────────────────────────────────────────────────────────

    public function savedByUsers(): HasMany
    {
        return $this->hasMany(UserSavedSense::class);
    }

    public function userExamples(): HasMany
    {
        return $this->hasMany(UserSavedExample::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_sense')
            ->using(CollectionSense::class)
            ->withPivot('sort_order', 'mastery_level', 'added_at')
            ->withTimestamps();
    }

    public function aiUsageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class);
    }

    public function affirmations(): HasMany
    {
        return $this->hasMany(Affirmation::class);
    }

    public function disputations(): HasMany
    {
        return $this->hasMany(Disputation::class);
    }

    // ── Grammar patterns (bidirectional link) ─────────────────────────────────

    public function grammarPatterns(): BelongsToMany
    {
        return $this->belongsToMany(GrammarPattern::class, 'grammar_pattern_word_senses')
            ->withPivot('role', 'sort_order', 'editorial_note')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
