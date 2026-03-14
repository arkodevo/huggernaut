<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per character · per system · per reading.
        // 行 = 4 rows: xíng/pinyin, háng/pinyin, ㄒㄧㄥˊ/zhuyin, ㄏㄤˊ/zhuyin.
        Schema::create('word_pronunciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_object_id')
                ->constrained('word_objects')
                ->cascadeOnDelete();
            $table->foreignId('pronunciation_system_id')
                ->constrained('pronunciation_systems');
            $table->string('pronunciation_text');
            $table->boolean('is_primary')->default(false);
            $table->string('dialect_region')->nullable();
            $table->string('audio_file')->nullable();
            $table->timestamps();

            $table->index(['word_object_id', 'pronunciation_system_id']);
            $table->index('pronunciation_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_pronunciations');
    }
};
