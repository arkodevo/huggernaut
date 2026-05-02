<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Allow NULL on pos_label_translations.label so a row can be preserved with
// the slug-language pair intact while the human-facing label is still being
// authored. Use case (2026-04-26): re-purposing the CE slug from
// "Chinese Idiom" → "Complement Expression"; the zh-TW label needs to be
// re-translated by 絡一. Preserving the row (with NULL label) is preferable
// to deleting it — the row says "this language is acknowledged for this slug,
// translation pending" rather than disappearing entirely.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_label_translations', function (Blueprint $table) {
            $table->string('label', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pos_label_translations', function (Blueprint $table) {
            $table->string('label', 255)->nullable(false)->change();
        });
    }
};
