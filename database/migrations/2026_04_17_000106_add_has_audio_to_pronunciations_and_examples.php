<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks which neural TTS voice files have been pre-generated for each
     * row. JSONB shape: { "tw-f": true, "tw-m": true, "cn-f": false, "cn-m": false }
     *
     * Four voices generated via edge-tts using Microsoft's commercial neural
     * voices (zh-TW-HsiaoChenNeural, zh-TW-YunJheNeural, zh-CN-XiaoxiaoNeural,
     * zh-CN-YunxiNeural). Files land at:
     *   storage/app/public/audio/pronunciations/{tw-f|tw-m|cn-f|cn-m}/{id}.mp3
     *   storage/app/public/audio/examples/{tw-f|tw-m|cn-f|cn-m}/{id}.mp3
     *
     * JSONB (vs four boolean columns) so we can add voices later (a male
     * sense-specific voice, a human recording variant, etc.) without schema
     * churn. Query via `->has_audio->>'tw-f' = 'true'` when needed.
     */
    public function up(): void
    {
        Schema::table('word_pronunciations', function (Blueprint $table) {
            $table->jsonb('has_audio')->default('{}')->after('is_primary');
        });

        Schema::table('word_sense_examples', function (Blueprint $table) {
            $table->jsonb('has_audio')->default('{}')->after('is_suppressed');
        });
    }

    public function down(): void
    {
        Schema::table('word_pronunciations', function (Blueprint $table) {
            $table->dropColumn('has_audio');
        });

        Schema::table('word_sense_examples', function (Blueprint $table) {
            $table->dropColumn('has_audio');
        });
    }
};
