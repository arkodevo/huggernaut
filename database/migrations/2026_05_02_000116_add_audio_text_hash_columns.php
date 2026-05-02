<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// audio_text_hash is sha256(source_text) at the time the audio files were
// written. The freshness check is `has_audio[slot] === true AND
// audio_text_hash === sha256(current_text)`. A mismatch (or null) means the
// stored MP3s no longer match the text and must be regenerated.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('word_pronunciations', function (Blueprint $table) {
            $table->char('audio_text_hash', 64)->nullable()->after('has_audio');
        });

        Schema::table('word_sense_examples', function (Blueprint $table) {
            $table->char('audio_text_hash', 64)->nullable()->after('has_audio');
        });
    }

    public function down(): void
    {
        Schema::table('word_pronunciations', function (Blueprint $table) {
            $table->dropColumn('audio_text_hash');
        });

        Schema::table('word_sense_examples', function (Blueprint $table) {
            $table->dropColumn('audio_text_hash');
        });
    }
};
