<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── note_types ────────────────────────────────────────────────────────
        // Extensible type registry for word sense notes.
        // Initial types: formula, usage-note, learner-traps.
        // Adding a new note type = seed a row here. No schema migration needed.

        Schema::create('note_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── note_type_labels ──────────────────────────────────────────────────
        // i18n labels for note_types.

        Schema::create('note_type_labels', function (Blueprint $table) {
            $table->foreignId('note_type_id')->constrained('note_types')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('label');
            $table->timestamps();

            $table->primary(['note_type_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_type_labels');
        Schema::dropIfExists('note_types');
    }
};
