<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── pos_groups ─────────────────────────────────────────────────────────
        // Simplified display groupings for POS labels.
        // Maps granular TOCFL POS (Vi, Vp, Vs…) to learner-friendly categories
        // (Verb, Adjective, Noun…). Used for card display and future filter modes.

        Schema::create('pos_groups', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();      // e.g. verb, adjective, noun
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── pos_group_labels ───────────────────────────────────────────────────
        // i18n labels for pos_groups.

        Schema::create('pos_group_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_group_id')->constrained('pos_groups')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('label');
            $table->timestamps();

            $table->unique(['pos_group_id', 'language_id']);
        });

        // ── pos_labels.group_id ────────────────────────────────────────────────
        // FK linking each POS label to its simplified display group.
        // Nullable so top-level grouping labels (V) can remain ungrouped.

        Schema::table('pos_labels', function (Blueprint $table) {
            $table->foreignId('group_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('pos_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_labels', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::dropIfExists('pos_group_labels');
        Schema::dropIfExists('pos_groups');
    }
};
