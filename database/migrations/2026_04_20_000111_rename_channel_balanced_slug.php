<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Rename channel-balanced designation → balanced (2026-04-20).
//
// The other four channel slugs don't carry the attribute-name prefix
// (spoken-only, spoken-dominant, written-dominant, written-only).
// channel-balanced was an outlier — the `channel-` prefix duplicates
// information already in the attribute slug (channel). Renaming to
// `balanced` brings the set to consistent shape.
//
// Foreign-key rows (word_senses.channel_id) are unaffected — they point
// to the designation.id, which doesn't change. Only the slug string moves.
//
// Code/prompt/template/view updates are shipped alongside this migration.

return new class extends Migration {
    public function up(): void
    {
        DB::table('designations')
            ->where('slug', 'channel-balanced')
            ->where('attribute_id', DB::table('attributes')->where('slug', 'channel')->value('id'))
            ->update(['slug' => 'balanced']);
    }

    public function down(): void
    {
        DB::table('designations')
            ->where('slug', 'balanced')
            ->where('attribute_id', DB::table('attributes')->where('slug', 'channel')->value('id'))
            ->update(['slug' => 'channel-balanced']);
    }
};
