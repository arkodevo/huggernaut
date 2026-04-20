<?php

namespace App\Services\Enrichment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * FrozenSets — DB-backed single source of truth for all enumerated slug sets
 * used in enrichment, import validation, admin forms, and 師父 prompts.
 *
 * The name "frozen" reflects the discipline: slugs never rename, and every
 * tool references this one registry rather than hand-coding sets that drift.
 *
 * Born from batch L4-01 2026-04-17, where a hand-coded reviewer set diverged
 * from the DB and caused 22 wrong slug reverts before being caught at import.
 * Never again.
 *
 * All methods return flat arrays of slug strings, cached 1 hour. Call
 * ::forget() after a seeder run to invalidate.
 */
class FrozenSets
{
    private const CACHE_TTL      = 3600;
    private const CACHE_PREFIX   = 'frozen_sets';

    /** Category attribute slug → cache key */
    private const CATEGORY_KEYS = [
        'domain'        => 'domains',
        'channel'       => 'channels',
        'connotation'   => 'connotations',
        'dimension'     => 'dimensions',
        'register'      => 'registers',
        // 'semantic-mode' retired 2026-04-20 — dimension covers the axis.
        'sensitivity'   => 'sensitivities',
        'tocfl-level'   => 'tocfl_levels',
        'hsk-level'     => 'hsk_levels',
        'intensity'     => 'intensities',
    ];

    // ── Public accessors ─────────────────────────────────────────────────────

    public static function domains(): array        { return self::forCategory('domain'); }
    public static function channels(): array       { return self::forCategory('channel'); }
    public static function connotations(): array   { return self::forCategory('connotation'); }
    public static function dimensions(): array     { return self::forCategory('dimension'); }
    public static function registers(): array      { return self::forCategory('register'); }
    public static function sensitivities(): array  { return self::forCategory('sensitivity'); }
    public static function tocflLevels(): array    { return self::forCategory('tocfl-level'); }
    public static function hskLevels(): array      { return self::forCategory('hsk-level'); }

    /** POS labels live in a separate table, not designations. */
    public static function posLabels(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . ':pos_labels',
            self::CACHE_TTL,
            fn () => DB::table('pos_labels')->pluck('slug')->sort()->values()->all()
        );
    }

    /**
     * All frozen sets keyed by category. Useful for AI prompt injection
     * and admin form population.
     */
    public static function all(): array
    {
        $all = [];
        foreach (self::CATEGORY_KEYS as $attrSlug => $cacheKey) {
            $all[$cacheKey] = self::forCategory($attrSlug);
        }
        $all['pos_labels'] = self::posLabels();
        return $all;
    }

    // ── Membership tests ─────────────────────────────────────────────────────

    public static function isValidDomain(string $slug): bool       { return in_array($slug, self::domains(), true); }
    public static function isValidChannel(string $slug): bool      { return in_array($slug, self::channels(), true); }
    public static function isValidConnotation(string $slug): bool  { return in_array($slug, self::connotations(), true); }
    public static function isValidDimension(string $slug): bool    { return in_array($slug, self::dimensions(), true); }
    public static function isValidRegister(string $slug): bool     { return in_array($slug, self::registers(), true); }
    public static function isValidPosLabel(string $slug): bool     { return in_array($slug, self::posLabels(), true); }

    // ── Cache control ────────────────────────────────────────────────────────

    /** Invalidate all frozen-set caches. Call after designations/pos_labels seed. */
    public static function forget(): void
    {
        foreach (self::CATEGORY_KEYS as $cacheKey) {
            Cache::forget(self::CACHE_PREFIX . ':' . $cacheKey);
        }
        Cache::forget(self::CACHE_PREFIX . ':pos_labels');
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private static function forCategory(string $attributeSlug): array
    {
        $cacheKey = self::CATEGORY_KEYS[$attributeSlug] ?? null;
        if (! $cacheKey) {
            throw new \InvalidArgumentException("Unknown attribute category: {$attributeSlug}");
        }

        return Cache::remember(
            self::CACHE_PREFIX . ':' . $cacheKey,
            self::CACHE_TTL,
            fn () => DB::table('designations as d')
                ->join('attributes as a', 'd.attribute_id', '=', 'a.id')
                ->where('a.slug', $attributeSlug)
                ->pluck('d.slug')
                ->sort()
                ->values()
                ->all()
        );
    }
}
