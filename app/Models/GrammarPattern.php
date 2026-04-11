<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class GrammarPattern extends Model
{
    protected $fillable = [
        'slug',
        'chinese_label',
        'pattern_template',
        'grammar_pattern_group_id',
        'tocfl_level_id',
        'hsk_level_id',
        'status',
        'sort_order',
    ];

    // Cache key shared between `shifuReferenceList()` and its invalidators.
    // Bumped to v2 when the booted() invalidator was added — this effectively
    // forces a one-time refresh so previously-stale v1 entries are bypassed.
    public const SHIFU_REFERENCE_CACHE_KEY = 'grammar_patterns:shifu_reference_v2';

    // Invalidate the 師父 reference-list cache whenever a pattern is saved or
    // deleted. Without this, newly-published patterns were invisible to the
    // Writing Conservatory for up to 10 minutes after publish — causing 師父
    // to miss them entirely during learner critique.
    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::SHIFU_REFERENCE_CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    // ── Group ────────────────────────────────────────────────────────────────

    public function group(): BelongsTo
    {
        return $this->belongsTo(GrammarPatternGroup::class, 'grammar_pattern_group_id');
    }

    // ── Level banding ────────────────────────────────────────────────────────

    public function tocflLevel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'tocfl_level_id');
    }

    public function hskLevel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'hsk_level_id');
    }

    // ── i18n ─────────────────────────────────────────────────────────────────

    public function labels(): HasMany
    {
        return $this->hasMany(GrammarPatternLabel::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(GrammarPatternNote::class);
    }

    // ── Content ──────────────────────────────────────────────────────────────

    public function examples(): HasMany
    {
        return $this->hasMany(GrammarPatternExample::class)->orderBy('sort_order');
    }

    // ── Vocabulary linking (bidirectional) ────────────────────────────────────

    public function wordSenses(): BelongsToMany
    {
        return $this->belongsToMany(WordSense::class, 'grammar_pattern_word_senses')
            ->withPivot('role', 'sort_order', 'editorial_note')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function markerSenses(): BelongsToMany
    {
        return $this->belongsToMany(WordSense::class, 'grammar_pattern_word_senses')
            ->wherePivot('role', 'marker')
            ->withPivot('role', 'sort_order', 'editorial_note')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function keyVocabSenses(): BelongsToMany
    {
        return $this->belongsToMany(WordSense::class, 'grammar_pattern_word_senses')
            ->wherePivot('role', 'key_vocab')
            ->withPivot('role', 'sort_order', 'editorial_note')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    // ── Multi-select taxonomy ────────────────────────────────────────────────

    public function designations(): BelongsToMany
    {
        return $this->belongsToMany(Designation::class, 'grammar_pattern_designations')
            ->withTimestamps();
    }

    // ── Inter-pattern relations ──────────────────────────────────────────────

    public function relatedPatterns(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'grammar_pattern_relations',
            'grammar_pattern_id',
            'related_pattern_id'
        )->withPivot('relation_type', 'editorial_note', 'sort_order')
         ->withTimestamps();
    }

    public function relatedFrom(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'grammar_pattern_relations',
            'related_pattern_id',
            'grammar_pattern_id'
        )->withPivot('relation_type', 'editorial_note', 'sort_order')
         ->withTimestamps();
    }

    // ── Vocabulary examples tagged with this pattern ─────────────────────────

    public function taggedWordExamples(): BelongsToMany
    {
        return $this->belongsToMany(
            WordSenseExample::class,
            'word_sense_example_grammar_patterns',
            'grammar_pattern_id',
            'word_sense_example_id'
        )->withTimestamps();
    }

    // ── Suggestions resolved to this pattern ─────────────────────────────────

    public function suggestions(): HasMany
    {
        return $this->hasMany(GrammarPatternSuggestion::class);
    }

    // ── User bookmarks ───────────────────────────────────────────────────────

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_saved_grammar_patterns')
            ->withPivot('personal_note', 'saved_at')
            ->withTimestamps();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // ── 師父 reference list ──────────────────────────────────────────────────
    // Compact list of patterns + their marker word surface forms, for injection
    // into the Writing Conservatory critique prompt. Cached for 10 minutes since
    // grammar patterns change rarely.
    public static function shifuReferenceList(): array
    {
        return Cache::remember(self::SHIFU_REFERENCE_CACHE_KEY, 600, function () {
            $patterns = self::query()
                ->whereIn('status', ['draft', 'review', 'published'])
                ->with([
                    'labels',
                    'notes',
                    'markerSenses.wordObject:id,traditional',
                ])
                ->orderBy('sort_order')
                ->get();

            return $patterns->map(function (GrammarPattern $p) {
                $enLabel = $p->labels->firstWhere('language_id', 1)?->name
                    ?? $p->labels->first()?->name
                    ?? $p->chinese_label;
                $enNote = $p->notes->firstWhere('language_id', 1);

                // Prefer explicit linked marker senses; fall back to CJK characters
                // extracted from the pattern_template (filters out Latin placeholders
                // like [V], [Subj], 'X', 'VP', punctuation, etc.).
                $markers = $p->markerSenses
                    ->map(fn ($ws) => $ws->wordObject?->traditional)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($markers) && $p->pattern_template) {
                    preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $p->pattern_template, $m);
                    $markers = array_values(array_unique($m[0] ?? []));
                }

                return [
                    'slug'     => $p->slug,
                    'zh_label' => $p->chinese_label,
                    'en_label' => $enLabel,
                    'formula'  => $enNote?->formula ?? '',
                    'markers'  => $markers,
                ];
            })->values()->all();
        });
    }
}
